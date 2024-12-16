<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

class ThinnedProcess extends Entity
{
    public static $schema = "zmsentities/schema/citizenapi/thinnedProcess.json";

    // Add properties based on schema (or dynamically handle them in Entity class)

    /**
     * Convert the ThinnedProcess object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'processId' => $this->processId ?? null,
            'timestamp' => $this->timestamp ?? null,
            'authKey' => $this->authKey ?? null,
            'familyName' => $this->familyName ?? null,
            'customTextfield' => $this->customTextfield ?? null,
            'email' => $this->email ?? null,
            'telephone' => $this->telephone ?? null,
            'officeName' => $this->officeName ?? null,
            'officeId' => $this->officeId ?? null,
            'scope' => $this->scope ?? null,
            'subRequestCounts' => $this->subRequestCounts ?? [],
            'serviceId' => $this->serviceId ?? null,
            'serviceCount' => $this->serviceCount ?? 0,
        ];
    }
}
