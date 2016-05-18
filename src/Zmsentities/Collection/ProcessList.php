<?php
namespace BO\Zmsentities\Collection;

class ProcessList extends Base
{

    public function addProcesses($data)
    {
        foreach ($data as $process) {
            if ($process instanceof \BO\Zmsentities\Process) {
                $this[] = clone $process;
            }
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
}
