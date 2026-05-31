<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class LocationService extends Base
{
    /**
     * @var string[]
     *
     * @psalm-var array{location: 'location_id', service_id: 'service_id', locale: 'locale', 'appointment.slots': 'appointment_slots', 'appointment.link': 'appointment_link', 'appointment.external': 'appointment_external', 'appointment.multiple': 'appointment_multiple', 'appointment.allowed': 'appointment_bookable', hint: 'appointment_note', contact: 'contact_json'}
     */
    protected array $fieldMapping = [
        'location' => 'location_id',
        'service_id' => 'service_id',
        'locale' => 'locale',
        'appointment.slots' => 'appointment_slots',
        'appointment.link' => 'appointment_link',
        'appointment.external' => 'appointment_external',
        'appointment.multiple' => 'appointment_multiple',
        'appointment.allowed' => 'appointment_bookable',
        'hint' => 'appointment_note',
        'contact' => 'contact_json'
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['location_id', 'service_id', 'locale'],
                    array_values($this->get(['location', 'service_id', 'locale']))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
