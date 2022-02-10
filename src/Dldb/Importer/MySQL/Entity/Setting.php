<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Setting extends Base
{
    protected $fieldMapping = [
        'name' => 'name',
        'value' => 'value',
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['name'], array_values((array)$this->get('name')))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
