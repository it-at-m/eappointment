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
        $freeProcessesDate = null,
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
        $calendar = $this->readResolvedDays($calendar, $freeProcessesDate, $now, $slotType, $slotsRequired);
        return $calendar;
    }

    /**
     * Resolve calendar scopes
     *
     */
    protected function readResolvedScopes(Entity $calendar)
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
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
        $requestReader = new Request($this->getWriter(), $this->getReader());
        //if (! isset($calendar['processing']['slotinfo'])) {
        //    $calendar['processing']['slotinfo'] = [];
        //}
        foreach ($calendar['requests'] as $key => $request) {
            $request = $requestReader->readEntity('dldb', $request['id']);
            $calendar['requests'][$key] = $request;
            foreach ($requestReader->readSlotsOnEntity($request) as $slotinfo) {
                //if (! isset($calendar['processing']['slotinfo'][$slotinfo['provider__id']])) {
                //    $calendar['processing']['slotinfo'][$slotinfo['provider__id']] = 0;
                //}
                //$calendar['processing']['slotinfo'][$slotinfo['provider__id']] += $slotinfo['slots'];
                $calendar->scopes->addRequiredSlots('dldb', $slotinfo['provider__id'], $slotinfo['slots']);
            }
        }
        return $calendar;
    }

    protected function readResolvedClusters(Entity $calendar)
    {
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
        foreach ($calendar['clusters'] as $cluster) {
            $scopeList = $scopeReader->readByClusterId($cluster['id'], 1);
            foreach ($scopeList as $scope) {
                if (! $calendar['scopes']->hasEntity($scope->id)) {
                    $calendar['scopes']->addEntity($scope);
                }
            }
        }
        return $calendar;
    }

    protected function readResolvedProviders(Entity $calendar)
    {
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
        $providerReader = new Provider($this->getWriter(), $this->getReader());
        foreach ($calendar['providers'] as $key => $provider) {
            $calendar['providers'][$key] = $providerReader->readEntity('dldb', $provider['id']);
            $scopeList = $scopeReader->readByProviderId($provider['id'], 2);
            foreach ($scopeList as $scope) {
                if (! $calendar['scopes']->hasEntity($scope->id)) {
                    $calendar['scopes']->addEntity($scope);
                }
            }
        }
        return $calendar;
    }

    protected function readResolvedDays(
        Entity $calendar,
        $freeProcessesDate,
        \DateTimeInterface $now,
        $slotType,
        $slotsRequiredForce
    ) {
        $dayList = (new Day())->readByCalendar($calendar, $now);
        $calendar->days = $dayList;
        var_dump("$calendar");
        return $calendar;
    }
}
