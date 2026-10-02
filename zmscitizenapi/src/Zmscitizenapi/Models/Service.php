<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmscitizenapi\Models\Combinable;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class Service extends Entity implements JsonSerializable
{
    public static $schema = 'citizenapi/service.json';
    public int $id;
    public string $name;
    public ?int $maxQuantity = null;
    public ?Combinable $combinable = null;
    public ?int $parentId = null;
    public ?int $variantId = null;
    public ?int $rootParentId = null;
    public ?bool $showOnStartPage = null;
    public ?array $variantOverwrite = null;

    public function __construct(
        int $id,
        string $name,
        ?int $maxQuantity = null,
        ?Combinable $combinable = null,
        ?int $parentId = null,
        ?int $variantId = null,
        ?bool $showOnStartPage = null,
        ?int $rootParentId = null,
        ?array $variantOverwrite = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->maxQuantity = $maxQuantity;
        $this->combinable = $combinable;
        $this->parentId = $parentId;
        $this->variantId = $variantId;
        $this->showOnStartPage = $showOnStartPage;
        $this->rootParentId = $rootParentId ?? $id;
        $this->variantOverwrite = $variantOverwrite;
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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'maxQuantity' => $this->maxQuantity,
            'combinable' => $this->combinable,
            'parentId' => $this->parentId,
            'variantId' => $this->variantId,
            'rootParentId' => $this->rootParentId,
            'showOnStartPage' => $this->showOnStartPage
        ];
        if ($this->variantOverwrite !== null) {
            $data['variantOverwrite'] = $this->variantOverwrite;
        }
        return $data;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
