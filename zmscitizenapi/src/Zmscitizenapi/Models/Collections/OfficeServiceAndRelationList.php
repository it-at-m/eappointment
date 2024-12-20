<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class OfficeServiceAndRelationList extends Entity implements JsonSerializable
{
    public static $schema = "zmsentities/schema/citizenapi/collections/officeServiceAndRelationList.json";

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
    }

    public function toArray(): array
    {
        return [
            'offices' => $this->offices->toArray()['offices'],
            'services' => $this->services->toArray()['services'],
            'relations' => $this->relations->toArray()['relations']
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
