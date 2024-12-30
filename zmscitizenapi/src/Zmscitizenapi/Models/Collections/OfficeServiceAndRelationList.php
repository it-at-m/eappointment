<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class OfficeServiceAndRelationList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/officeServiceAndRelationList.json";

    /** @var OfficeList */
    protected OfficeList $offices;

    /** @var ServiceList */
    protected ServiceList $services;

    /** @var OfficeServiceRelationList */
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

    private function ensureValid()
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException("The provided data is invalid according to the schema.");
        }
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
