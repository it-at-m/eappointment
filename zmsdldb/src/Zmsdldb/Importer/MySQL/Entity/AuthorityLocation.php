<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class AuthorityLocation extends Base
{
    protected array $fieldMapping = [
        'authority_id' => 'authority_id',
        'id' => 'location_id',
        'locale' => 'locale'
    ];

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
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
