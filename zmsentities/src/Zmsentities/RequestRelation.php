<?php

namespace BO\Zmsentities;

class RequestRelation extends Schema\Entity
{
    public static string $schema = "requestrelation.json";

    /**
     * @return (Provider|Request|null|string|true)[]
     *
     * @psalm-return array{provider: Provider, request: Request, source: null, slots: '1', public: true, maxQuantity: null}
     */
    public function getDefaults()
    {
        return [
            'provider' => new Provider(),
            'request' => new Request(),
            'source' => null,
            'slots' => '1',
            'public' => true,
            'maxQuantity' => null,
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

    public function getMaxQuantity()
    {
        return $this->toProperty()->maxQuantity->get();
    }

    public function isPublic(): bool
    {
        return (bool) $this->toProperty()->public->get();
    }

    public function getSource()
    {
        return $this->toProperty()->source->get();
    }
}
