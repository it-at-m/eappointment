<?php

namespace BO\Zmsentities;

class Request extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "request.json";

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
                if (! $provider['appointment']['external'] && $provider['appointment']['allowed']) {
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
}
