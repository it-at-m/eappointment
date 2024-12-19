<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class OfficeServiceAndRelationList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/officesAndServices.json";

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
            'offices' => $this->offices->toArray()['offices'], // Extract only the 'offices' data
            'services' => $this->services->toArray()['services'], // Extract only the 'services' data
            'relations' => $this->relations->toArray()['relations'], // Extract only the 'relations' data
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
