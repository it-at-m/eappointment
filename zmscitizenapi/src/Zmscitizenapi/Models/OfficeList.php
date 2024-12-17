<?php

namespace BO\Zmscitizenapi\Models;

use \BO\Zmsentities\Schema\Entity;

class OfficeList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/offices.json";

    protected array $offices = [];
    public int $status; // Explicitly include status property

    public function __construct(array $data = [], int $status = 200)
    {
        $this->offices = $data['offices'] ?? [];
        $this->status = $status;
    }

    /**
     * Convert OfficeList object to an array
     */
    public function toArray(): array
    {
        return [
            "offices" => $this->offices,
            "status" => $this->status
        ];
    }
}
