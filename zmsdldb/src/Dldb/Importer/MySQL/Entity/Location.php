<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Location extends Base
{
    protected $fieldMapping = [
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
    protected function setupMapping()
    {
        $this->referanceMapping = [
            'name' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
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
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
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
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
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
                'multiple' => false,
                'clearFields' => ['type' => static::getTableName(), 'locale' => $this->get('meta.locale')],
            ],
            'contact' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Contact',
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
            'name' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'name' => 'search_value'
                ],
                'addFields' => [
                    'entity_type' => static::getTableName(),
                    'search_type' => 'name'
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
        ];
    }

    public function preSetupFields()
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

    public function preSetup()
    {
        try {
            /*
            if (false === $this->get('meta.translated')) {
                $this->setStatus(static::STATUS_OLD);
                error_log(
                    'not translated location - (' . $this->get('id') . ' | ' .
                    $this->get('meta.locale') . ') - ' . $this->get('name')
                );
                return false;
            }
            else {
                error_log(
                    'translated location - (' . $this->get('id') . ' | ' .
                    $this->get('meta.locale') . ') - ' . $this->get('name')
                );
            }
            */
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
