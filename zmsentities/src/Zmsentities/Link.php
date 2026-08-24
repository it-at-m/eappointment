<?php

namespace BO\Zmsentities;

class Link extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = "link.json";

    /**
     * @return (bool|int|string)[]
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'name' => '',
            'url' => '',
            'target' => true,
            'public' => false,
            'organisation' => 0
        ];
    }

    public function __toString()
    {
        return "Link {$this->name}-{$this->url}- with target " . $this->target;
    }
}
