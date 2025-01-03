<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Models\Collections;

use BO\Zmscitizenapi\Models\Service;
use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ServiceList extends Entity implements JsonSerializable
{
    public static $schema = "citizenapi/collections/serviceList.json";

    /** @var Service[] */
    protected array $services = [];

    public function __construct(array $services = [])
    {
        foreach ($services as $service) {
            try {
                if (!$service instanceof Service) {
                    throw new InvalidArgumentException("Element is not an instance of Service.");
                }
                $this->services[] = $service;
            } catch (\Exception $e) {
                error_log("Invalid Service encountered: " . $e->getMessage()); //Gracefully handle
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
