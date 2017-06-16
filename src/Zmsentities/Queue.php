<?php

namespace BO\Zmsentities;

class Queue extends Schema\Entity implements Helper\NoSanitize
{
    const PRIMARY = 'arrivalTime';

    public static $schema = "queue.json";

    protected $process;

    public function getDefaults()
    {
        return [
            "arrivalTime" => 0,
            "callCount" => 0,
            "callTime" => 0,
            "number" => 0,
            "waitingTime" => 0
        ];
    }

    public function setProcess(Process $parentProcess)
    {
        $this->process = $parentProcess;
        return $this;
    }

    public function getProcess()
    {
        return clone $this->process;
    }

    /**
     * Keep empty, no sub-instances
     * ATTENTION: Keep highly optimized, time critical function
     */
    public function __clone()
    {
    }
}
