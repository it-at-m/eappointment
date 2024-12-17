<?php

namespace BO\Zmscitizenapi\Models;

use \BO\Zmsentities\Schema\Entity;

class ServiceList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/services.json";

    protected array $services = [];
    public int $status;

    public function __construct(array $data = [], int $status = 200)
    {
        $this->services = $data;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            "services" => $this->services,
            "status" => $this->status
        ];
    }
}
