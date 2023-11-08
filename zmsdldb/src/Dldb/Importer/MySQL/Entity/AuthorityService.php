<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class AuthorityService extends Base
{
    protected $fieldMapping = [
        'service_id' => 'service_id',
        'id' => 'authority_id',
        'locale' => 'locale'
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['service_id', 'authority_id', 'locale'],
                    array_values($this->get(['service_id', 'id', 'locale']))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
