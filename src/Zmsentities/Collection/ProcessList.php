<?php
namespace BO\Zmsentities\Collection;

class ProcessList extends Base
{

    public function addProcesses($data)
    {
        foreach ($data as $entity) {
            if ($entity instanceof \BO\Zmsentities\Process) {
                $this[] = clone $entity;
            }
        }
        return $this;
    }

    public function addProcess($entity)
    {
        if ($entity instanceof \BO\Zmsentities\Process) {
            $this[] = clone $entity;
        }
        return $this;
    }

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
        return current($this);
    }
}
