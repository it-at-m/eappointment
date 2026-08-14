<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class Location extends Base
{
    protected array $fieldMapping = [
        'id' => 'id',
        'name' => 'name',
        'category.name' => 'category_name',
        'category.identifier' => 'category_identifier',
        'authority.id' => 'authority_id',
        'authority.name' => 'authority_name',
        'meta.locale' => 'locale',
        'note' => 'note',
        'category' => 'category_json',
        'urgent' => 'urgent_json',
        'opening_times' => 'opening_times_json',
        'transit' => 'transit_json',
        'payment_info' => 'payment_json',
        'accessibility' => 'accessibility_json',
        'appointment' => 'appointment_json',
        '__RAW__' => 'data_json'
    ];
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[\Override]
    protected function setupMapping(): void
    {
        $this->referanceMapping = [
            'name' => [
                'class' => Search::class,
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'name' => 'search_value'
                ],
                'addFields' => [
                    'entity_type' => static::getTableName(),
                    'search_type' => 'name'
                ],
                'multiple' => false,
                'clearFields' => [
                    'entity_type' => static::getTableName(),
                    'locale' => $this->get('meta.locale')
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'entity_type' => static::getTableName()
                ],
                'selfAsArray' => true
            ],
            'address' => [
                'class' => Search::class,
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'address' => 'search_value',
                ],
                'addFields' => [
                    'entity_type' => static::getTableName(),
                    'search_type' => 'address'
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'entity_type' => static::getTableName()
                ],
                'multiple' => false,
                'clearFields' => [
                    'entity_type' => static::getTableName(),
                    'locale' => $this->get('meta.locale')
                ],
                'selfAsArray' => true
            ],
            'meta.keywords' => [
                'class' => Search::class,
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'meta.keywords' => 'search_value',
                ],
                'addFields' => [
                    'entity_type' => static::getTableName(),
                    'search_type' => 'keywords'
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'entity_type' => static::getTableName()
                ],
                'multiple' => false,
                'clearFields' => [
                    'entity_type' => static::getTableName(),
                    'locale' => $this->get('meta.locale')
                ],
                'selfAsArray' => true
            ],
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
                'multiple' => false,
                'clearFields' => ['type' => static::getTableName(), 'locale' => $this->get('meta.locale')],
            ],
            'contact' => [
                'class' => Contact::class,
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'address' => 'address',
                    'geo' => 'geo'
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale')
                ],
                'addFields' => [],
                'multiple' => false,
                'selfAsArray' => true,
                'clearFields' => ['locale' => $this->get('meta.locale')],
            ],
        ];
    }

    #[\Override]
    public function preSetupFields(): void
    {
        #$this->dataRaw['payment'] = [
        #    'payment_info' => $this->dataRaw['payment'],
        #    'paymentcode' => ($this->dataRaw['paymentcode'] ?? null)
        #];
        $this->dataRaw['payment_info'] = [
            'info' => $this->dataRaw['payment'],
            'code' => ($this->dataRaw['paymentcode'] ?? null)
        ];
        #unset($this->dataRaw['paymentcode']);
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
