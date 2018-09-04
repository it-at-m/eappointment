<?php

namespace BO\Zmsentities;

class RequestProvider extends Schema\Entity
{

    public static $schema = "requestprovider.json";

    public function getDefaults()
    {
        return [
            'provider' => new Provider(),
            'request' => new Request(),
            'source' => null,
            'slots' => '1',
        ];
    }

    public function getRequest()
    {
        return $this->toProperty()->request->get();
    }

    public function getProvider()
    {
        return $this->toProperty()->provider->get();
    }

    public function getSlotCount()
    {
        return $this->toProperty()->slots->get();
    }

    public function getSource()
    {
        return $this->toProperty()->source->get();
    }

    public function toRequestRelation()
    {
        $item = new self();
        $item->request = $this->getRequest();
        $item->slots = $this->getSlotCount();
        return $item;
    }

    public function toProviderRelation()
    {
        $item = new self();
        $item->provider = $this->getProvider();
        return $item;
    }
}
