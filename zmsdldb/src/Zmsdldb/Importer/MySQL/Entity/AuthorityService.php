<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class AuthorityService extends Base
{
    /**
     * @var string[]
     *
     * @psalm-var array{service_id: 'service_id', id: 'authority_id', locale: 'locale'}
     */
    protected array $fieldMapping = [
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
