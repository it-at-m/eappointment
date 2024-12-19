<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ThinnedScopeList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/thinnedScopeList.json";

    protected array $scopes = [];

    public function __construct(array $data = [])
    {
        $this->scopes = $data;
    }

    public function toArray(): array
    {
        return [
            "scopes" => $this->scopes,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
