<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Models\Collections;

use BO\Zmscitizenbackend\Models\ThinnedScope;
use BO\Zmscitizenbackend\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ThinnedScopeList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/thinnedScopeList.json";
    public array $scopes = [];
    public function __construct(array $scopes = [])
    {
        foreach ($scopes as $scope) {
            try {
                if (!$scope instanceof ThinnedScope) {
                    throw new InvalidArgumentException("Element is not an instance of ThinnedScope.");
                }
                $this->scopes[] = $scope;
            } catch (\Exception $e) {
                \App::$log->warning('Invalid ThinnedScope skipped', ['exception' => $e->getMessage()]);
            }
        }

        $this->ensureValid();
    }

    /**
     * @return void
     */
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

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }
}
