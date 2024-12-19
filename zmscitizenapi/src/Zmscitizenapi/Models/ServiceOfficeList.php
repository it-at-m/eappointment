<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ServiceOfficeList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/officesAndServices.json";

    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return array_merge($this->data);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
