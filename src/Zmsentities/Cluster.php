<?php

namespace BO\Zmsentities;

class Cluster extends Schema\Entity
{
    public static $schema = "cluster.json";

    public function getName()
    {
        if (array_key_exists('name', $this)) {
            return $this->name;
        }
    }
}
