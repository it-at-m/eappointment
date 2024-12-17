<?php

namespace BO\Zmscitizenapi\Models;

use \BO\Zmsentities\Schema\Entity;

class ServiceList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/services.json";

    protected array $data = [];
    public int $status;

    public function __construct(array $data = [], int $status = 200)
    {
        $this->data = $data;
        $this->status = $status;
    }

    /**
     * Convert ServiceList object to an array
     */
    public function toArray(): array
    {
        return array_merge($this->data, ["status" => $this->status]);
    }
}
