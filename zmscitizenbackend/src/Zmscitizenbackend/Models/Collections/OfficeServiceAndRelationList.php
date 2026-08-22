<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Models\Collections;

use BO\Zmscitizenbackend\Models\Collections\OfficeList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenbackend\Models\Collections\ServiceList;
use BO\Zmscitizenbackend\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class OfficeServiceAndRelationList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/officeServiceAndRelationList.json";
    protected OfficeList $offices;
    protected ServiceList $services;
    protected OfficeServiceRelationList $relations;
    public function __construct(OfficeList $offices, ServiceList $services, OfficeServiceRelationList $relations)
    {
        $this->offices = $offices;
        $this->services = $services;
        $this->relations = $relations;
        $this->ensureValid();
    }

    public function toArray(): array
    {
        return [
            'offices' => $this->offices->toArray()['offices'],
            'services' => $this->services->toArray()['services'],
            'relations' => $this->relations->toArray()['relations']
        ];
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

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
