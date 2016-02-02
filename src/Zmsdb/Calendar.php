<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Calendar as Entity;
use \BO\Zmsdb\Query\SlotList;

class Calendar extends Base
{

    public function readResolvedEntity(\BO\Zmsentities\Calendar $calendar)
    {
        $calendar['processing'] = [];
        $calendar['processing']['slotlist'] = new SlotList();
        $calendar = $this->readResolvedProviders($calendar);
        $calendar = $this->readResolvedClusters($calendar);
        $calendar = $this->readResolvedRequests($calendar);
        $calendar = $this->readResolvedDays($calendar);
        unset($calendar['processing']);
        return $calendar;
    }

    protected function readResolvedRequests(\BO\Zmsentities\Calendar $calendar)
    {
        $requestReader = new Request($this->getWriter(), $this->getReader());
        if (!isset($calendar['processing']['slotinfo'])) {
            $calendar['processing']['slotinfo'] = [];
        }
        foreach ($calendar['requests'] as $key => $request) {
            $request = $requestReader->readEntity('dldb', $request['id']);
            $calendar['requests'][$key] = $request;
            foreach ($requestReader->readSlotsOnEntity($request) as $slotinfo) {
                if (!isset($calendar['processing']['slotinfo'][$slotinfo['provider__id']])) {
                    $calendar['processing']['slotinfo'][$slotinfo['provider__id']] = 0;
                }
                $calendar['processing']['slotinfo'][$slotinfo['provider__id']] += $slotinfo['slots'];
            }
        }
        return $calendar;
    }

    protected function readResolvedClusters(\BO\Zmsentities\Calendar $calendar)
    {
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
        foreach ($calendar['clusters'] as $cluster) {
            $scopeList = $scopeReader->readByClusterId($cluster['id']);
            foreach ($scopeList as $scope) {
                $calendar['scopes'][] = $scope;
            }
        }
        return $calendar;
    }

    protected function readResolvedProviders(\BO\Zmsentities\Calendar $calendar)
    {
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
        $providerReader = new Provider($this->getWriter(), $this->getReader());
        foreach ($calendar['providers'] as $key => $provider) {
            $calendar['providers'][$key] = $providerReader->readEntity('dldb', $provider['id']);
            $scopeList = $scopeReader->readByProviderId($provider['id']);
            foreach ($scopeList as $scope) {
                $calendar['scopes'][] = $scope;
            }
        }
        return $calendar;
    }

    protected function readResolvedDays(\BO\Zmsentities\Calendar $calendar)
    {
        $query = SlotList::getQuery();
        $monthList = $calendar->getMonthList();
        $statement = $this->getReader()->prepare($query);
        foreach ($monthList as $monthDateTime) {
            $month =  new \DateTimeImmutable($monthDateTime->format('c'));
            foreach ($calendar->scopes as $scope) {
                $statement->execute(SlotList::getParameters($scope['id'], $monthDateTime));
                $slotsRequired = $calendar['processing']['slotinfo'][$scope->getProviderId()];
                while ($slotData = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $calendar = $this->addDayInfoToCalendar($calendar, $slotData, $month, $slotsRequired);
                }
            }
        }
        return $calendar;
    }

    /**
     *
     * ATTENTION: performance critical function, keep highly optimized!
     */
    protected function addDayInfoToCalendar(
        \BO\Zmsentities\Calendar $calendar,
        array $slotData,
        \DateTimeImmutable $month,
        $slotsRequired
    ) {
        $slotlist =& $calendar['processing']['slotlist'];
        if (!$slotlist->isSlotData($slotData)) {
            $slotlist->toReducedBySlots($slotsRequired);
            $calendar = $slotlist->addToCalendar($calendar);
            $calendar['processing']['slotlist'] = new SlotList(
                $slotData,
                $month->modify('first day of'),
                $month->modify('last day of')
            );
        } else {
            $slotlist->addSlotData($slotData);
        }
        return $calendar;
    }
}
