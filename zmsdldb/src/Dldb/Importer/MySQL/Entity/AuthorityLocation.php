<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class AuthorityLocation extends Base
{
    protected $fieldMapping = [
        'authority_id' => 'authority_id',
        'id' => 'location_id',
        'locale' => 'locale'
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['authority_id', 'location_id', 'locale'],
                    array_values($this->get(['authority_id', 'id', 'locale']))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
