<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class ServiceInformation extends Base
{
    /**
     * @var string[]
     *
     * @psalm-var array{service_id: 'service_id', locale: 'locale', name: 'name', description: 'description', link: 'link', type: 'type', sort: 'sort'}
     */
    protected array $fieldMapping = [
        'service_id' => 'service_id',
        'locale' => 'locale',
        'name' => 'name',
        'description' => 'description',
        'link' => 'link',
        'type' => 'type',
        'sort' => 'sort'
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['service_id', 'locale', 'type'],
                    array_values($this->get('service_id', 'locale', 'type'))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
