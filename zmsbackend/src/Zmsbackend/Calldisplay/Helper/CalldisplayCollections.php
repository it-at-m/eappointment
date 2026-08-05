<?php

namespace BO\Zmsbackend\Calldisplay\Helper;

use BO\Zmsentities\Calldisplay as Entity;
use BO\Zmsentities\Collection\ClusterList;
use BO\Zmsentities\Collection\ScopeList;

/**
 * Keep calldisplay requests stable when some configured scope/cluster ids no longer exist.
 */
class CalldisplayCollections
{
    /**
     * Drop missing clusters/scopes (log each), or throw if nothing valid remains.
     *
     * @return array<int|string, mixed> resolved scopes keyed by id (for queue cache)
     * @SuppressWarnings(NPathComplexity)
     */
    public static function retainExisting(Entity $calldisplay, callable $readScope, string $logPrefix = 'Calldisplay'): array
    {
        $hadScopes = $calldisplay->hasScopeList();
        $hadClusters = $calldisplay->hasClusterList();
        if (! $hadScopes && ! $hadClusters) {
            throw new \BO\Zmsbackend\Calldisplay\Exception\ScopeAndClusterNotFound();
        }

        self::keepExistingClusters($calldisplay, $logPrefix);
        $resolved = self::keepExistingScopes($calldisplay, $readScope, $logPrefix);
        self::assertHasScopeOrCluster($calldisplay, $hadScopes, $hadClusters);

        return $resolved;
    }

    private static function keepExistingClusters(Entity $calldisplay, string $logPrefix): void
    {
        $clusterList = new ClusterList();
        foreach ($calldisplay->getClusterList() as $clusterRef) {
            $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($clusterRef->getId());
            if (! $cluster) {
                \App::$log->warning($logPrefix . ': skip missing cluster id', [
                    'clusterId' => $clusterRef->getId(),
                ]);
                continue;
            }
            $clusterList->addEntity($cluster);
        }
        $calldisplay->clusters = $clusterList;
    }

    private static function keepExistingScopes(Entity $calldisplay, callable $readScope, string $logPrefix): array
    {
        $scopeList = new ScopeList();
        $resolved = [];
        foreach ($calldisplay->getScopeList() as $scopeRef) {
            $scope = $readScope($scopeRef);
            if (! $scope) {
                \App::$log->warning($logPrefix . ': skip missing scope id', [
                    'scopeId' => $scopeRef->getId(),
                ]);
                continue;
            }
            $scopeList->addEntity($scope);
            $resolved[$scope->getId()] = $scope;
        }
        $calldisplay->scopes = $scopeList;
        return $resolved;
    }

    private static function assertHasScopeOrCluster(Entity $calldisplay, bool $hadScopes, bool $hadClusters): void
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
