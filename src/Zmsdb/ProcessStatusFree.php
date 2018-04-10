<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Collection\ProcessList as Collection;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessStatusFree extends Process
{
    public function readFreeProcesses(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeInterface $now,
        $slotType = 'public',
        $slotsRequired = 0
    ) {
        //$resolvedCalendar = new Calendar();
        $calendar = (new Calendar())->readResolvedEntity($calendar, $now, true);
        (new Day)->writeTemporaryScopeList($calendar, $slotsRequired);
        $selectedDate = $calendar->getFirstDay();
        $processList = new Collection();
        //$calendar->setLastDayTime($selectedDate);
        //$calendar = $resolvedCalendar->readResolvedEntity($calendar, $now, $selectedDate, $slotType, $slotsRequired);
        $processData = $this->getReader()
            ->fetchAll(
                Query\ProcessStatusFree::QUERY_SELECT_PROCESSLIST_DAY,
                [
                    'day' => $selectedDate->format('d'),
                    'slotType' => $slotType,
                ]
            );
        foreach ($processData as $item) {
            $process = new \BO\Zmsentities\Process($item);
            $process->requests = $calendar->requests;
            $process->appointments->getFirst()->setDateByString(
                $process->appointments->getFirst()->date,
                'Y-m-d H:i:s'
            );
            $process->scope = $calendar->scopes->getEntity($process->scope->id);
            $process->appointments->getFirst()->scope = $process->scope;
            $processList->addEntity($process);
            //var_dump("$process");
        }
        $this->getReader()->exec(Query\Day::QUERY_DROP_TEMPORARY_SCOPELIST);
        return $processList;
    }


    public function readReservedProcesses($resolveReferences = 2)
    {
        $processList = new Collection();
        $query = new Query\Process(Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionAssigned()
            ->addConditionIsReserved();
        $resultData = $this->fetchList($query, new Entity());
        foreach ($resultData as $process) {
            if (2 == $resolveReferences) {
                $process['requests'] = (new Request())->readRequestByProcessId($process->id, $resolveReferences);
                $process['scope'] = (new Scope())->readEntity($process->getScopeId(), $resolveReferences);
            }
            if ($process instanceof Entity) {
                $processList->addEntity($process);
            }
        }
        return $processList;
    }

    /**
     * Insert a new process if there are free slots
     *
     * @param \BO\Zmsentities\Process $process
     * @param \DateTimeInterface $now
     * @param String $slotType
     * @param Int $slotsRequired we cannot use process.appointments.0.slotCount, because setting slotsRequired is
     *        a priviliged operation. Just using the input would be a security flaw to get a wider selection of times
     *        If slotsRequired = 0, readFreeProcesses() uses the slotsRequired based on request-provider relation
     */
    public function writeEntityReserved(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        $slotType = "public",
        $slotsRequired = 0
    ) {
        $process->status = 'reserved';
        $appointment = $process->getAppointments()->getFirst();
        $freeProcessList = $this->readFreeProcesses($process->toCalendar(), $now, $slotType, $slotsRequired);
        $slotList = (new Slot)->readByAppointment($appointment);
        if (!$freeProcessList->getAppointmentList()->hasAppointment($appointment) || ! $slotList) {
            throw new Exception\Process\ProcessReserveFailed();
        }

        foreach ($slotList as $slot) {
            if ($process->id > 99999) {
                $newProcess = clone $process;
                $newProcess->getFirstAppointment()->setTime($slot->time);
                $this->writeNewProcess($newProcess, $now, $process->id);
            } elseif ($process->id === 0) {
                $process = $this->writeNewProcess($process, $now, 0, count($slotList) - 1);
            } else {
                throw new \Exception("SQL UPDATE error on inserting new $process on $slot");
            }
        }
        $this->writeRequestsToDb($process);
        return $process;
    }
}
