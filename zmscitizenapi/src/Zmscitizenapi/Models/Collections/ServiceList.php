<?php

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\Service;
use BO\Zmsentities\Schema\Entity;
use JsonSerializable;

class ServiceList extends Entity implements JsonSerializable
{
    public static $schema = "zmsentities/schema/citizenapi/collections/serviceList.json";

    /** @var Service[] */
    protected array $services = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $service) {
            if (!$service instanceof Service) {
                throw new \InvalidArgumentException("All elements must be instances of Service.");
            }
        }
        $this->services = $data;
    }

    /**
     * Converts the service list to an array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "services" => array_map(fn(Service $service) => $service->toArray(), $this->services)
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
