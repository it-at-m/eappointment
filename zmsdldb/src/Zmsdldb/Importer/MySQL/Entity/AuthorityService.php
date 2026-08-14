<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class AuthorityService extends Base
{
    protected array $fieldMapping = [
        'service_id' => 'service_id',
        'id' => 'authority_id',
        'locale' => 'locale'
    ];

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
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
