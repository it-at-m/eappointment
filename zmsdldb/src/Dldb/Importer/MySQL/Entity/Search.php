<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Search extends Base
{
    protected $fieldMapping = [
        'object_id' => 'object_id',
        'locale' => 'locale',
        'entity_type' => 'entity_type',
        'search_type' => 'search_type',
        'search_value' => 'search_value',
    ];

    public function postSetupFields()
    {
        if (array_key_exists('search_value', ($this->fields ?? [])) && !empty($this->fields['search_value'])) {
            if (is_array($this->fields['search_value'])) {
                $this->fields['search_value'] = implode(', ', array_filter($this->fields['search_value']));
            }
        }
        $this->fields['search_value'] = trim($this->fields['search_value']);
    }

    public function postSetup()
    {
        $val = trim($this->fields['search_value']);
        if (empty($val)) {
            $this->setStatus(static::STATUS_OLD);
        }
    }

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['object_id', 'locale', 'entity_type'],
                    array_values($this->get(['object_id', 'locale', 'entity_type']))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []): bool
    {
        try {
            #print_r(static::class);
            return $this->deleteWith(
                array_combine(['entity_type', 'locale'], array_values($this->get(['entity_type', 'locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
