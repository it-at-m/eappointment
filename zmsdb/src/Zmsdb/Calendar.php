<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Calendar as Entity;
use \BO\Zmsdb\Query\SlotList;

/**
 * @SuppressWarnings(Coupling)
 *
 */
class Calendar extends Base
{
    public function readResolvedEntity(
        Entity $calendar,
        \DateTimeInterface $now,
        $resolveOnlyScopes = false,
        $slotType = 'public',
        $slotsRequired = 0
    ) {
        $calendar['freeProcesses'] = new \BO\Zmsentities\Collection\ProcessList();
        $calendar['scopes'] = $calendar->getScopeList();
        $calendar = $this->readResolvedScopes($calendar);
        $calendar = $this->readResolvedProviders($calendar);
        $calendar = $this->readResolvedClusters($calendar);
        $calendar = $this->readResolvedRequests($calendar);
        $calendar = $this->readResolvedScopeReferences($calendar);
        if (count($calendar->scopes) < 1) {
            throw new Exception\CalendarWithoutScopes("No scopes resolved in $calendar");
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
        $scopeReader = new Scope();
        foreach ($calendar->scopes as $scope) {
            $scope = $scopeReader->readEntity($scope['id'], 2);
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
            $scope['dayoff'] = (new DayOff())->readByScopeId($scope['id']);
        }
        return $calendar;
    }

    protected function readResolvedRequests(Entity $calendar)
    {
        $requestReader = new Request();
        $requestRelationQuery = new RequestRelation();
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
        $scopeReader = new Scope();
        foreach ($calendar['clusters'] as $cluster) {
            $cluster = new \BO\Zmsentities\Cluster($cluster);
            $scopeList = $scopeReader->readByClusterId($cluster->getId(), 1);
            foreach ($scopeList as $scope) {
                if (! $calendar['scopes']->hasEntity($scope->getId())) {
                    $calendar['scopes']->addEntity($scope);
                }
            }
        }
        return $calendar;
    }

    protected function readResolvedProviders(Entity $calendar)
    {
        $scopeReader = new Scope();
        $providerReader = new Provider();
        foreach ($calendar['providers'] as $key => $provider) {
            $provider = new \BO\Zmsentities\Provider($provider);
            $calendar['providers'][$key] = $providerReader->readEntity($provider->getSource(), $provider->getId());
            $scopeList = $scopeReader->readByProviderId($provider->getId(), 2);
            foreach ($scopeList as $scope) {
                $scope = new \BO\Zmsentities\Scope($scope);
                if (! $calendar['scopes']->hasEntity($scope->getId())) {
                    $calendar['scopes']->addEntity($scope);
                }
            }
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
            $dayQuery = new Day();
            $dayList = $dayQuery->readByCalendar($calendar, $slotsRequiredForce);
            $calendar->days = $dayList->setStatusByType($slotType, $now);
            $bookableEndString = $this->getReader()->fetchValue(Query\Calendar::QUERY_CALENDAR_BOOKABLEEND);
            $calendar->bookableEnd = (new \DateTimeImmutable($bookableEndString, $now->getTimezone()))->getTimestamp();
        }
        return $calendar;
    }
}
