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
}
