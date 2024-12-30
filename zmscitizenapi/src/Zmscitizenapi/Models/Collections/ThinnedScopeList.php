<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ThinnedScopeList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/thinnedScopeList.json";

    /** @var ThinnedScope[] */
    protected array $scopes = [];

    public function __construct(array $scopes = [])
    {
        foreach ($scopes as $scope) {
            try {
                if (!$scope instanceof ThinnedScope) {
                    throw new InvalidArgumentException("Element is not an instance of ThinnedScope.");
                }
                $this->scopes[] = $scope;
            } catch (\Exception $e) {
                error_log("Invalid ThinnedScope encountered: " . $e->getMessage()); //Gracefully handle
            }
        }

        $this->ensureValid();
    }

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
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

    public function getScopes(): array
    {
        return $this->scopes;
    }

}
