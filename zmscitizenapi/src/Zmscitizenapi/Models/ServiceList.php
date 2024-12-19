<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ServiceList extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/serviceList.json";

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

    /**
     * Implements JSON serialization.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
