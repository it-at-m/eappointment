<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Authority extends Base
{
    protected $fieldMapping = [
        'id' => 'id',
        'name' => 'name',
        'parent_id' => 'parent_id',
        'meta.locale' => 'locale',
        'locations' => 'locations_json',
        'relation' => 'relation_json',
        'contact' => 'contact_json',
        '__RAW__' => 'data_json'
    ];

    protected function setupMapping()
    {
        $this->referanceMapping = [
            'meta' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Meta',
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale'
                ],
                'addFields' => [
                    'type' => static::getTableName()
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => static::getTableName()
                ],
                'clearFields' => ['type' => static::getTableName(), 'locale' => $this->get('meta.locale')],
                'multiple' => false
            ],
            'locations' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\AuthorityLocation',
                'neededFields' => ['id' => 'authority_id', 'meta.locale' => 'locale'],
                'addFields' => [

                ],
                'deleteFields' => [
                    'authority_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale')
                ],
                'clearFields' => [
                    'locale' => $this->get('meta.locale')
                ]
            ],
        ];
    }

    public function preSetupFields()
    {
        $this->dataRaw['parent_id'] = ($this->dataRaw['parent_id'] ?? 0);
    }

    public function preSetup()
    {
        try {
            $fields = $this->get(['id', 'meta.locale', 'meta.hash']);
            $fields[] = static::getTableName();
            $this->setStatus(static::STATUS_OLD);
            if ($this->itemNeedsUpdate(...array_values($fields))) {
                $this->setStatus(static::STATUS_NEW);
                $this->setupFields();
                $this->deleteEntity();
                $this->deleteReferences();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['id', 'locale'], array_values($this->get(['id', 'meta.locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []): bool
    {
        try {
            return $this->deleteWith(
                ['locale' => $this->get('meta.locale')]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
