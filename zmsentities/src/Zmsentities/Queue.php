<?php

namespace BO\Zmsentities;

class Queue extends Schema\Entity implements Helper\NoSanitize
{
    public const PRIMARY = 'arrivalTime';

    public static ?string $schema = "queue.json";

    /**
     * @var Process|null
     */
    protected $process;

    /**
     * @return (int|null)[]
     *
     * @psalm-return array{arrivalTime: 0, callCount: 0, callTime: 0, number: 0, waitingTimeEstimate: 0, waitingTimeOptimistic: 0, waitingTime: 0, wayTime: 0, priority: null}
     */
    public function getDefaults()
    {
        return [
            "arrivalTime" => 0,
            "callCount" => 0,
            "callTime" => 0,
            "number" => 0,
            "waitingTimeEstimate" => 0,
            "waitingTimeOptimistic" => 0,
            "waitingTime" => 0,
            "wayTime" => 0,
            "priority" => null
        ];
    }

    public function setProcess(Process $parentProcess): static
    {
        $this->process = $parentProcess;
        return $this;
    }

    public function getProcess(): Process|null
    {
        if ($this->process instanceof Process) {
            $process = clone $this->process;
            $process->queue = clone $this;
            return $process;
        }
        return null;
    }

    public function withReference($additionalData = [])
    {
        return clone $this;
    }

    /**
     * Keep empty, no sub-instances
     * ATTENTION: Keep highly optimized, time critical function
     */
    public function __clone()
    {
    }
}
