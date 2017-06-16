<?php

namespace BO\Zmsentities;

class Link extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "link.json";

    public function getDefaults()
    {
        return [
            'name' => '',
            'url' => '',
            'target' => true
        ];
    }

    public function __toString()
    {
        return "Link {$this->name}-{$this->url}- with target ". $this->target;
    }
}
