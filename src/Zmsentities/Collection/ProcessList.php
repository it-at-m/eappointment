<?php
namespace BO\Zmsentities\Collection;

class ProcessList extends Base
{
    public function toProcessListByTime()
    {
        $list = new self();
        foreach ($this as $process) {
            $appointment = $process->getFirstAppointment();
            $list[$appointment['date']][] = clone $process;
        }
        return $list;
    }

    public function getFirstProcess()
    {
        return reset($this);
    }

    public function getScopeList()
    {
        $list = new ScopeList();
        foreach ($this as $process) {
            if (array_key_exists('scope', $process)) {
                $list[] = new \BO\Zmsentities\Scope($process['scope']);
            }
        }
        return $list->withUniqueScopes();
    }

    public function getAppointmentList()
    {
        $appointmentList = new AppointmentList();
        foreach ($this as $process) {
            foreach ($process["appointments"] as $appointment) {
                $appointmentList->addEntity(new \BO\Zmsentities\Appointment($appointment));
            }
        }
        return $appointmentList;
    }

    //check if necessary
    /*
    public function toReducedWithinTime($referenceTime)
    {
        $processList = new self();
        foreach ($this as $process) {
            if ($process->getFirstAppointment()['date'] < $referenceTime) {
                $processList->addEntity($process);
            }
        }
        return $processList;
    }
    */

    /*
    public function toQueue($now)
    {

        $queue = new QueueList();
        foreach ($this as $process) {
            $queue->addEntity($process->queue);
        }
        return $queue;
    }
    */
}
