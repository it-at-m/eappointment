<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Calendar as Entity;
use \BO\Zmsdb\Query\SlotList;

class Calendar extends Base
{

    public function readResolvedEntity(
        Entity $calendar,
        \DateTimeInterface $now,
        $freeProcessesDate = null,
        $slotType = 'public'
    ) {
        $calendar['processing'] = [];
        $calendar['freeProcesses'] = new \BO\Zmsentities\Collection\ProcessList();
        $calendar['scopes'] = $calendar->getScopeList();
        $calendar['processing']['slotlist'] = new SlotList();
        $calendar = $this->readResolvedScopes($calendar);
        $calendar = $this->readResolvedProviders($calendar);
        $calendar = $this->readResolvedClusters($calendar);
        $calendar = $this->readResolvedRequests($calendar);
        $calendar = $this->readResolvedScopeReferences($calendar);
        $calendar = $this->readResolvedDays($calendar, $freeProcessesDate, $now, $slotType);
        unset($calendar['processing']);
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
            $scope['dayoff'] = (new DayOff())->readByScopeId($scope['id']);
        }
        return $calendar;
    }

    protected function readResolvedRequests(Entity $calendar)
    {
        $requestReader = new Request($this->getWriter(), $this->getReader());
        if (! isset($calendar['processing']['slotinfo'])) {
            $calendar['processing']['slotinfo'] = [];
        }
        foreach ($calendar['requests'] as $key => $request) {
            $request = $requestReader->readEntity('dldb', $request['id']);
            $calendar['requests'][$key] = $request;
            foreach ($requestReader->readSlotsOnEntity($request) as $slotinfo) {
                if (! isset($calendar['processing']['slotinfo'][$slotinfo['provider__id']])) {
                    $calendar['processing']['slotinfo'][$slotinfo['provider__id']] = 0;
                }
                $calendar['processing']['slotinfo'][$slotinfo['provider__id']] += $slotinfo['slots'];
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
            $scopeList = $scopeReader->readByProviderId($provider['id'], 1);
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
        $slotType = 'public'
    ) {
        $querySlotList = new SlotList();
        $query = SlotList::getQuery();
        $monthList = $calendar->getMonthList();
        $statement = $this->getReader()->prepare($query);
        foreach ($monthList as $month) {
            $monthDateTime = $calendar->getDateTimeFromDate($month);
            foreach ($calendar->scopes as $scope) {
                if ($freeProcessesDate) {
                    $statement->execute(SlotList::getParametersDay($scope['id'], $freeProcessesDate, $now));
                } else {
                    $statement->execute(SlotList::getParameters($scope['id'], $monthDateTime, $now));
                }
                $slotsRequired = $calendar['processing']['slotinfo'][$scope->getProviderId()];
                while ($slotData = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $calendar = $this->addDayInfoToCalendar(
                        $calendar,
                        $querySlotList->postProcess($slotData),
                        $monthDateTime,
                        $slotsRequired,
                        $freeProcessesDate,
                        $now,
                        $scope,
                        $slotType
                    );
                    //error_log("|".$slotData['slottime'].":".$slotData['slotdate'].":".$slotData['availability__id']);
                }
                // Process the last processed slotlist missed by addDayInfoToCalendar
                $calendar = $this->addDayInfoToCalendar(
                    $calendar,
                    ['availability__id' => null],
                    $monthDateTime,
                    $slotsRequired,
                    $freeProcessesDate,
                    $now,
                    $scope,
                    $slotType
                );
                $calendar['processing']['slotlist'] = new SlotList();
            }
        }
        return $calendar;
    }

    /**
     * ATTENTION: performance critical function, keep highly optimized!
     */
    protected function addDayInfoToCalendar(
        Entity $calendar,
        array $slotData,
        \DateTimeImmutable $monthDateTime,
        $slotsRequired,
        $freeProcessesDate,
        $now,
        \BO\Zmsentities\Scope $scope = null,
        $slotType = 'public'
    ) {
        if (! $calendar['processing']['slotlist']->isSameAvailability($slotData)) {
            $calendar['processing']['slotlist']->toReducedBySlots($slotsRequired);
            $calendar['processing']['slotlist']->addToCalendar($calendar, $freeProcessesDate, $slotType);
            if (null !== $slotData["availability__id"]) {
                $calendar['processing']['slotlist'] = new SlotList(
                    $slotData,
                    ($freeProcessesDate) ?
                        $freeProcessesDate->modify('00:00:00')
                            : $monthDateTime->modify('first day of')->modify('00:00:00'),
                    ($freeProcessesDate) ?
                        $freeProcessesDate->modify('23:59:59')
                            : $monthDateTime->modify('last day of')->modify('23:59:59'),
                    $now,
                    null,
                    $scope
                );
            }
        } elseif ($slotData['availability__id'] !== null) { //avoid two empty scopes in a row
            $calendar['processing']['slotlist']->addQueryData($slotData);
        }
        return $calendar;
    }
}
