<?php

namespace BO\Zmsentities;

class RequestRelation extends Schema\Entity
{
    public static $schema = "requestrelation.json";

    /**
     * @return (Provider|Request|null|string|true)[]
     *
     */
    #[\Override]
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

    public function getRequest(): mixed
    {
        return $this->toProperty()->request->get();
    }

    public function getProvider(): mixed
    {
        return $this->toProperty()->provider->get();
    }

    public function getSlotCount(): mixed
    {
        return $this->toProperty()->slots->get();
    }

    public function getMaxQuantity(): mixed
    {
        return $this->toProperty()->maxQuantity->get();
    }

    public function isPublic(): bool
    {
        return (bool) $this->toProperty()->public->get();
    }

    public function getSource(): mixed
    {
        return $this->toProperty()->source->get();
    }
}
