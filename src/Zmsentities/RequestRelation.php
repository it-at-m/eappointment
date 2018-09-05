<?php

namespace BO\Zmsentities;

class RequestRelation extends Schema\Entity
{

    public static $schema = "requestrelation.json";

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
        unset($item->provider);
        return $item;
    }

    public function toProviderRelation()
    {
        $item = new self();
        $item->provider = $this->getProvider();
        unset($item->request);
        return $item;
    }
}
