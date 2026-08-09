<?php

namespace BO\Zmsbackend\Calldisplay\Helper;

use BO\Zmsentities\Calldisplay as Entity;
use BO\Zmsentities\Collection\ClusterList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Scope;

/**
 * Drop missing scope/cluster ids from a calldisplay request so one bad id
 * cannot take down the whole display.
 */
class CalldisplayCollections
{
    public static function prepareForGet(Entity $calldisplay): void
    {
        self::prepare($calldisplay, 'Calldisplay', 0, false);
    }

    /**
     * @return array<int|string, Scope>
     */
    public static function prepareForQueue(Entity $calldisplay, int $resolveReferences): array
    {
        return self::prepare($calldisplay, 'Calldisplay queue', $resolveReferences, true);
    }

    /**
     * @return array<int|string, Scope>
     */
    private static function prepare(
        Entity $calldisplay,
        string $logPrefix,
        int $resolveReferences,
        bool $withWorkstationCount
    ): array {
        $hadScopes = $calldisplay->hasScopeList();
        $hadClusters = $calldisplay->hasClusterList();
        if (! $hadScopes && ! $hadClusters) {
            throw new \BO\Zmsbackend\Calldisplay\Exception\ScopeAndClusterNotFound();
        }

        self::filterClusters($calldisplay, $logPrefix);
        $scopeCache = self::filterScopes($calldisplay, $logPrefix, $resolveReferences, $withWorkstationCount);
        self::assertAnythingLeft($calldisplay, $hadScopes, $hadClusters);

        return $scopeCache;
    }

    private static function filterClusters(Entity $calldisplay, string $logPrefix): void
    {
        $clusterList = new ClusterList();
        $clusterService = new \BO\Zmsbackend\Cluster\Service\Cluster();

        foreach ($calldisplay->getClusterList() as $clusterRef) {
            $cluster = $clusterService->readEntity($clusterRef->getId());
            if ($cluster) {
                $clusterList->addEntity($cluster);
                continue;
            }
            \App::$log->warning($logPrefix . ': skip missing cluster id', [
                'clusterId' => $clusterRef->getId(),
            ]);
        }

        $calldisplay['clusters'] = $clusterList;
    }

    /**
     * @return array<int|string, Scope>
     */
    private static function filterScopes(
        Entity $calldisplay,
        string $logPrefix,
        int $resolveReferences,
        bool $withWorkstationCount
    ): array {
        $scopeList = new ScopeList();
        $scopeCache = [];
        $scopeService = new \BO\Zmsbackend\Scope\Service\Scope();

        foreach ($calldisplay->getScopeList() as $scopeRef) {
            $scopeId = $scopeRef->getId();
            $scope = $withWorkstationCount
                ? $scopeService->readWithWorkstationCount($scopeId, \App::$now, $resolveReferences)
                : $scopeService->readEntity($scopeId);

            if ($scope instanceof Scope) {
                $scopeList->addEntity($scope);
                $scopeCache[$scope->getId()] = $scope;
                continue;
            }
            \App::$log->warning($logPrefix . ': skip missing scope id', [
                'scopeId' => $scopeId,
            ]);
        }

        $calldisplay['scopes'] = $scopeList;
        return $scopeCache;
    }

    private static function assertAnythingLeft(Entity $calldisplay, bool $hadScopes, bool $hadClusters): void
    {
        if ($calldisplay->hasScopeList() || $calldisplay->hasClusterList()) {
            return;
        }
        if ($hadScopes && ! $hadClusters) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        if ($hadClusters && ! $hadScopes) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }
        throw new \BO\Zmsbackend\Calldisplay\Exception\ScopeAndClusterNotFound();
    }
}
