<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class OfficeList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/officeList.json";

    protected array $offices = [];

    public function __construct(array $data = [])
    {
        $this->offices = $data;
    }

    public function toArray(): array
    {
        return [
            "offices" => $this->offices
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
