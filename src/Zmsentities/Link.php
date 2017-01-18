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
            'link' => '',
            'target' => true
        ];
    }

    public function __toString()
    {
        return "Link {$this->name}-{$this->link}- with target ". $this->target;
    }
}
