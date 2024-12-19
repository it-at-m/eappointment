<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ServiceList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/serviceList.json";

    protected array $services = [];

    public function __construct(array $data = [])
    {
        $this->services = $data;
    }

    public function toArray(): array
    {
        return [
            "services" => $this->services
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
