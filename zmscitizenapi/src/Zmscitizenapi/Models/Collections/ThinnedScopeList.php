<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class ThinnedScopeList extends Entity implements JsonSerializable
{
    public static $schema = "zmsentities/schema/citizenapi/collections/thinnedScopeList.json";

    /** @var ThinnedScope[] */
    protected array $scopes = [];

    public function __construct(array $scopes = [])
    {
        foreach ($scopes as $scope) {
            if (!$scope instanceof ThinnedScope) {
                throw new \InvalidArgumentException("All elements must be instances of ThinnedScope.");
            }
        }
        $this->scopes = $scopes;
    }

    public function toArray(): array
    {
        return [
            'scopes' => array_map(fn(ThinnedScope $scope) => $scope->toArray(), $this->scopes),
        ];
    }    

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
