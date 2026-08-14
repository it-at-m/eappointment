<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class Setting extends Base
{
    protected array $fieldMapping = [
        'name' => 'name',
        'value' => 'value',
    ];

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
                array_combine(['name'], array_values((array)$this->get('name')))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
