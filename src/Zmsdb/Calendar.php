<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Calendar as Entity;

class Calendar extends Base
{

    public function readResolvedEntity(\BO\Zmsentities\Calendar $calendar)
    {
        $calendar['processing'] = [];
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
        $query = 'SELECT
                UNIX_TIMESTAMP(CONCAT(b.Datum, " ", b.Uhrzeit)) AS appointment__date,
                s.StandortID AS appointment__scope__id,
                s.mehrfachtermine AS appointment__scope__preferences__appointment__multipleSlotsEnabled,
                DAYOFMONTH(b.Datum) AS `day`,
                MONTH(b.Datum) AS `month`,
                YEAR(b.Datum) AS `year`,
                GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet - COUNT(b.Datum))
                    AS `freeAppointments__public`,
                GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter - COUNT(b.Datum))
                    AS `freeAppointments__callcenter`,
                o.Anzahlterminarbeitsplaetze - COUNT(b.Datum)
                    AS `freeAppointments__intern`,
                FLOOR(((TIME_TO_SEC(b.Uhrzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot))) AS `slotnr`,
                o.erlaubemehrfachslots AS availability__multipleSlotsAllowed,
                o.allexWochen AS availability__repeat__afterWeeks,
                o.jedexteWoche AS availability__repeat__weekOfMonth,
                FLOOR(TIME_TO_SEC(o.Timeslot) / 60) AS availability__slotTimeInMinutes,
                UNIX_TIMESTAMP(o.Startdatum) AS availability__startDate,
                o.Terminanfangszeit	 AS availability__startTime
            FROM
                standort s
                LEFT JOIN oeffnungszeit o USING(StandortID)
                LEFT JOIN buerger b ON
                    b.StandortID = o.StandortID
                    AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)
                    AND b.Uhrzeit >= o.Anfangszeit
                    AND b.Uhrzeit <= o.Endzeit
                    AND b.Datum >= o.Startdatum
                    AND b.Datum <= o.Endedatum
            WHERE
                o.StandortID = :scope_id
                AND b.Datum IS NOT NULL
                AND b.Datum BETWEEN :start_process AND :end_process
                AND o.Endedatum >= :start_availability
                AND o.Startdatum <= :end_availability
                AND o.Anzahlterminarbeitsplaetze != 0
            GROUP BY o.OeffnungszeitID, b.Datum, `slotnr`
            HAVING
                -- reduce results cause processing them costs time
                freeAppointments__intern != 0
        ';
        $monthList = $calendar->getMonthList();
        $statement = $this->getReader()->prepare($query);
        foreach ($monthList as $monthDateTime) {
            foreach ($calendar->scopes as $scope) {
                $statement->execute([
                    'scope_id' => $scope->id,
                    'start_process' => $monthDateTime->format('Y-m-1'),
                    'end_process' => $monthDateTime->format('Y-m-t'),
                    'start_availability' => $monthDateTime->format('Y-m-1'),
                    'end_availability' => $monthDateTime->format('Y-m-t'),
                ]);
                while ($dayInfo = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $calendar = $this->addDayInfoToCalendar($calendar, $dayInfo);
                }
            }
        }
        return $calendar;
    }

    /**
     *
     * ATTENTION: performance critical function, keep highly optimized!
     */
    public function addDayInfoToCalendar(\BO\Zmsentities\Calendar $calendar, array $dayInfo)
    {
        $day = $calendar->getDay($dayInfo['year'], $dayInfo['month'], $dayInfo['day']);
        /* TODO check slots (calendar[processing]) and
          if availability is true on this day (week series, Termin_ab, Termine_bis)
        */
        $day['freeAppointments']['public'] += $dayInfo['freeAppointments__public'];
        $day['freeAppointments']['intern'] += $dayInfo['freeAppointments__intern'];
        $day['freeAppointments']['callcenter'] += $dayInfo['freeAppointments__callcenter'];
        return $calendar;
    }
}
