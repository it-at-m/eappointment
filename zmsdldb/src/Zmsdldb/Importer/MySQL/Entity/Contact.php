<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class Contact extends Base
{
    protected array $fieldMapping = [
        'object_id' => 'object_id',
        'locale' => 'locale',
        'name' => 'name',
        'contact' => 'contact_json',
        'address' => 'address_json',
        'deviating_postal_address' => 'deviating_postal_address_json',
        'geo' => 'geo_json',
    ];

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
                array_combine(['object_id', 'locale'], array_values($this->get(['object_id', 'locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
