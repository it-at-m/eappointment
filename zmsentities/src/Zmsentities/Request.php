<?php

namespace BO\Zmsentities;

class Request extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static $schema = "request.json";

    public function getDefaults()
    {
        return [
            'id' => '123',
            'name' => '',
            'source' => 'dldb'
        ];
    }

    public function withReference($additionalData = [])
    {
        $additionalData['id'] = $this->getId();
        $additionalData['name'] = $this->getName();
        return parent::withReference($additionalData);
    }

    public function hasAppointmentFromProviderData()
    {
        if (isset($this['data']) && isset($this['data']['locations'])) {
            foreach ($this['data']['locations'] as $provider) {
                if (
                    (!isset($provider['appointment']['external']) || !$provider['appointment']['external'])
                    && isset($provider['appointment']['allowed']) && $provider['appointment']['allowed']
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getSource()
    {
        return $this->toProperty()->source->get();
    }

    public function getGroup()
    {
        return $this->toProperty()->group->get();
    }

    public function getLink()
    {
        return $this->toProperty()->link->get();
    }

    public function getName()
    {
        return $this->toProperty()->name->get();
    }

    public function getAdditionalData()
    {
        return $this->toProperty()->data->get();
    }

    public function __toString()
    {
        return $this->getName();
    }
}
