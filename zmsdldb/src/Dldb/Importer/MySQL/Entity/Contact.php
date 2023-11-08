<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Contact extends Base
{
    protected $fieldMapping = [
        'object_id' => 'object_id',
        'locale' => 'locale',
        'name' => 'name',
        'contact' => 'contact_json',
        'address' => 'address_json',
        'deviating_postal_address' => 'deviating_postal_address_json',
        'geo' => 'geo_json',
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['object_id', 'locale'], array_values($this->get(['object_id', 'locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
