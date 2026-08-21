<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class Authority extends Base
{
    protected array $fieldMapping = [
        'id' => 'id',
        'name' => 'name',
        'parent_id' => 'parent_id',
        'meta.locale' => 'locale',
        'locations' => 'locations_json',
        'relation' => 'relation_json',
        'contact' => 'contact_json',
        '__RAW__' => 'data_json'
    ];

    #[\Override]
    protected function setupMapping(): void
    {
        $this->referanceMapping = [
            'meta' => [
                'class' => Meta::class,
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
                'class' => AuthorityLocation::class,
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

    #[\Override]
    public function preSetupFields(): void
    {
        $this->dataRaw['parent_id'] = ($this->dataRaw['parent_id'] ?? 0);
    }

    #[\Override]
    public function preSetup(): void
    {
        try {
            $this->setStatus(static::STATUS_OLD);
            if ($this->entityNeedsUpdate()) {
                $this->setStatus(static::STATUS_NEW);
                $this->setupFields();
                $this->deleteEntity();
                $this->deleteReferences();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
                array_combine(['id', 'locale'], array_values($this->get(['id', 'meta.locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[\Override]
    public function clearEntity(array $addWhere = []): void
    {
        try {
            $this->deleteWith(
                ['locale' => $this->get('meta.locale')]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
