<?php

namespace BO\Zmsentities;

class Request extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = "request.json";

    /**
     * @return (null|string)[]
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'id' => '',
            'name' => '',
            'source' => 'dldb',
            'parent_id' => null,
            'root_parent_id' => null,
            'variant_id' => null
        ];
    }

    #[\Override]
    public function withReference($additionalData = []): self|array
    {
        $additionalData['id'] = $this->getId();
        $additionalData['name'] = $this->getName();
        return parent::withReference($additionalData);
    }

    public function hasAppointmentFromProviderData(): bool
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

    public function getSource(): mixed
    {
        return $this->toProperty()->source->get();
    }

    public function getGroup(): mixed
    {
        return $this->toProperty()->group->get();
    }

    public function getLink(): mixed
    {
        return $this->toProperty()->link->get();
    }

    public function getName(): mixed
    {
        return $this->toProperty()->name->get();
    }

    public function getAdditionalData(): mixed
    {
        return $this->toProperty()->data->get();
    }

    public function getParentId(): mixed
    {
        return $this->toProperty()->parent_id->get();
    }

    public function getRootParentId(): string
    {
        $rootParentId = $this->toProperty()->root_parent_id->get();
        if ($rootParentId !== null && $rootParentId !== '') {
            return (string) $rootParentId;
        }

        return (string) $this->getId();
    }

    public function getVariantId(): mixed
    {
        return $this->toProperty()->variant_id->get();
    }

    public function __toString()
    {
        return $this->getName();
    }
}
