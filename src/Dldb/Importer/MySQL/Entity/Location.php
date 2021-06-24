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
        'urgent' => 'urgent_json',
        'opening_times' => 'opening_times_json',
        'transit' => 'transit_json',
        'payment' => 'payment_json',
        'accessibility' => 'accessibility_json',
        'appointment' => 'appointment_json'
    ];

    protected function setupMapping() {
        $this->referanceMapping = [
            'meta' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Meta',
                'neededFields' => [
                    'id' => 'object_id', 
                    'meta.locale' => 'locale'
                ],
                'addFields' => ['type' => static::getTableName()],
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
        ];
    }

    public function preSetupFields() {
        $this->dataRaw['payment'] = [
            'payment_info' => $this->dataRaw['payment'],
            'paymentcode' => ($this->dataRaw['paymentcode'] ?? null)
        ];
        unset($this->dataRaw['paymentcode']);
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
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['id', 'locale'], array_values($this->get(['id', 'meta.locale'])))
            );
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []) : bool {
        try {
            return $this->deleteWith(
                ['locale' => $this->get('meta.locale')]
            );
        }
        catch (\Exception $e) {
            throw $e;
        }
    }
}