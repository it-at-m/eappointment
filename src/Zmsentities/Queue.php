<?php

namespace BO\Zmsentities;

class Queue extends Schema\Entity
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
}
