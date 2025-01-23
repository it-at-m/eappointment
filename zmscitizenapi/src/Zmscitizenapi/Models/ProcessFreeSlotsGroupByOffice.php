<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;
use InvalidArgumentException;
use JsonSerializable;

class ProcessFreeSlotsGroupByOffice extends Entity implements JsonSerializable
{

    public static $schema = 'citizenapi/processFreeSlotsGroupByOffice.json';

    /** @var array|null */
    public array|null $officeAppointments = [];

    /**
     * @param array $officeAppointments
     */
    public function __construct(array $officeAppointments = [])
    {

        $this->officeAppointments = $officeAppointments;

        $this->ensureValid();
    }

    private function ensureValid(): void
    {
        if (!$this->testValid()) {
            throw new InvalidArgumentException('The provided data is invalid according to the schema.');
        }
    }

    /**
     * Converts the model data back into an array for serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'offices' => array_map(function($appointments, $officeId) {
                return [
                    'officeId' => $officeId,
                    'appointments' => $appointments
                ];
            }, $this->officeAppointments, array_keys($this->officeAppointments))
        ];
    }

    /**
     * Implementation of JsonSerializable.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}