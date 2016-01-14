<?php

namespace BO\Zmsentities;

class Process extends Schema\Entity
{
    public static $schema = "process.json";

    public function getRequestIds()
    {
        $idList = array();
        foreach ($this['requests'] as $request) {
            $idList[] = $request['id'];
        }
        return $idList;
    }

    public function getRequestCSV()
    {
        return implode(',', $this->getRequestIds());
    }
}
