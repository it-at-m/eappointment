<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ServiceOfficeList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/officesAndServices.json";

    protected array $data = [];
    public int $status;

    public function __construct(array $data = [], int $status = 200)
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return array_merge($this->data, ["status" => $this->status]);
    }
}
