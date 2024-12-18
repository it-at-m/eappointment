<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ThinnedScopeList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/thinnedScopeList.json";

    protected array $scopes = [];
    public int $status;

    public function __construct(array $data = [], int $status = 200)
    {
        $this->scopes = $data;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            "scopes" => $this->scopes,
            "status" => $this->status
        ];
    }
}
