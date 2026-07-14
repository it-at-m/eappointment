<?php

namespace BO\Zmsbackend\Calendar\Service;

use BO\Zmsentities\Calendar as Entity;
use BO\Zmsbackend\Slot\Repository\SlotList;

/**
 * @SuppressWarnings(Coupling)
 *
 */
class Calendar extends \BO\Zmsbackend\Base
{
    public function readResolvedEntity(
        Entity $calendar,
        \DateTimeInterface $now,
        $resolveOnlyScopes = false,
        $slotType = 'public',
        $slotsRequired = 0,
        $resolveScopeReferences = true
    ) {
        $calendar['freeProcesses'] = new \BO\Zmsentities\Collection\ProcessList();
        $calendar['scopes'] = $calendar->getScopeList();
        $calendar = $this->readResolvedScopes($calendar);
        $calendar = $this->readResolvedProviders($calendar);
        $calendar = $this->readResolvedClusters($calendar);
        $calendar = $this->readResolvedRequests($calendar);
        if ($resolveScopeReferences) {
            $calendar = $this->readResolvedScopeReferences($calendar);
        }
        if (count($calendar->scopes) < 1) {
            throw new \BO\Zmsbackend\Calendar\Exception\CalendarWithoutScopes("No scopes resolved in $calendar");
        }
        $calendar = $this->readResolvedDays($calendar, $resolveOnlyScopes, $now, $slotType, $slotsRequired);
        return $calendar;
    }

    /**
     * Resolve calendar scopes
     *
     */
    protected function readResolvedScopes(Entity $calendar)
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeReader = new \BO\Zmsbackend\Scope\Service\Scope();
        foreach ($calendar->scopes as $scope) {
            $scope = $scopeReader->readEntity($scope['id'], 1);
            $scopeList->addEntity($scope);
        }
        $calendar['scopes'] = $scopeList->withUniqueScopes();
        return $calendar;
    }

    /**
     * Resolve Reference dayoff for departments, but do not resolve circular scope->department->scopes
     *
     */
    protected function readResolvedScopeReferences(Entity $calendar)
    {
        foreach ($calendar->scopes as $scope) {
            $scope['dayoff'] = (new \BO\Zmsbackend\Dayoff\Service\DayOff())->readByScopeId($scope['id']);
        }
        return $calendar;
    }

    protected function readResolvedRequests(Entity $calendar)
    {
        $requestReader = new \BO\Zmsbackend\Request\Service\Request();
        $requestRelationQuery = new \BO\Zmsbackend\RequestRelation\Service\RequestRelation();
        foreach ($calendar['requests'] as $key => $request) {
            $request = new \BO\Zmsentities\Request($request);
            $request = $requestReader->readEntity($request->getSource(), $request->getId());
            $calendar['requests'][$key] = $request;
            $requestRelationList = $requestRelationQuery->readListByRequestId($request->getId(), $request->getSource());
            foreach ($requestRelationList as $requestRelationItem) {
                // we do not check multipleSlotsEnabled here, because the availability might need this information
                // so we calculate slots as if multipleSlotsEnabled is true
                $calendar->scopes->addRequiredSlots(
                    $requestRelationItem->getSource(),
                    $requestRelationItem->getProvider()->getId(),
                    $requestRelationItem->getSlotCount()
                );
            }
        }
        return $calendar;
    }

    protected function readResolvedClusters(Entity $calendar)
    {
        $scopeReader = new \BO\Zmsbackend\Scope\Service\Scope();
        foreach ($calendar['clusters'] as $cluster) {
            $cluster = new \BO\Zmsentities\Cluster($cluster);
            $scopeList = $scopeReader->readByClusterId($cluster->getId(), 1);
            $calendar['scopes'] = $calendar['scopes']->addList($scopeList)->withUniqueScopes();
        }
        return $calendar;
    }

    protected function readResolvedProviders(Entity $calendar)
    {
        $scopeReader = new \BO\Zmsbackend\Scope\Service\Scope();
        $providerReader = new \BO\Zmsbackend\Provider\Service\Provider();
        foreach ($calendar['providers'] as $key => $provider) {
            $provider = new \BO\Zmsentities\Provider($provider);
            $calendar['providers'][$key] = $providerReader->readEntity($provider->getSource(), $provider->getId());
            $scopeList = $scopeReader->readByProviderId($provider->getId(), 1);
            $calendar['scopes'] = $calendar['scopes']->addList($scopeList)->withUniqueScopes();
        }
        return $calendar;
    }

    protected function readResolvedDays(
        Entity $calendar,
        $resolveOnlyScopes,
        \DateTimeInterface $now,
        $slotType,
        $slotsRequiredForce
    ) {
        if (!$resolveOnlyScopes) {
            $dayQuery = new \BO\Zmsbackend\Day\Service\Day();
            $dayList = $dayQuery->readByCalendar($calendar, $slotsRequiredForce);
            $calendar->days = $dayList->setStatusByType($slotType, $now);
            $bookableEndString = $this->getReader()->fetchValue(\BO\Zmsbackend\Calendar\Repository\Calendar::QUERY_CALENDAR_BOOKABLEEND);
            $calendar->bookableEnd = (new \DateTimeImmutable($bookableEndString === null ? '' : $bookableEndString, $now->getTimezone()))->getTimestamp();
        }
        return $calendar;
    }
}
