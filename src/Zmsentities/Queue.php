<?php

namespace BO\Zmsentities;

class Queue extends Schema\Entity
{
    public static $schema = "queue.json";

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
