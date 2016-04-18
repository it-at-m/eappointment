<?php

namespace BO\Zmsentities;

class Request extends Schema\Entity
{
    public static $schema = "request.json";

    public function getProviderIds()
    {
        $list = array();
        foreach ($this->data['locations'] as $item) {
            $list[] = $item['location'];
        }
        return $list;
    }
}
