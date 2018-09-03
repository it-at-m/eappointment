<?php

namespace BO\Zmsentities;

class RequestRelation extends Schema\Entity
{

    public static $schema = "requestrelation.json";

    public function getDefaults()
    {
        return [
            'request' => new Request(),
            'slots' => '1',
        ];
    }
}
