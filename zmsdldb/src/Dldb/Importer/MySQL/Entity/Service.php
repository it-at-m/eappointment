<?php

namespace BO\Dldb\Importer\MySQL\Entity;

use Error;

class Service extends Base
{
    protected $fieldMapping = [
        'id' => 'id',
        'name' => 'name',
        'hint' => 'hint',
        'leika' => 'leika',
        'fees' => 'fees',
        'appointment.link' => 'appointment_all_link',
        'responsibility' => 'responsibility',
        'responsibility_all' => 'responsibility_all',
        'description' => 'description',
        'processing_time' => 'processing_time',
        'relation.root_topic' => 'root_topic_id',
        'meta.locale' => 'locale',
        'residence' => 'residence',
        'representation' => 'representation',
        'authorities' => 'authorities_json',
        'onlineprocessing' => 'onlineprocessing_json',
        'relation' => 'relation_json',
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
            /*
            'description' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'description' => 'search_value'
                ],
                'addFields' => [
                    'entity_type' => static::getTableName(),
                    'search_type' => 'description'
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
            */
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
                'neededFields' => ['id' => 'object_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => static::getTableName()
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => static::getTableName()
                ],
                'multiple' => false,
                'clearFields' => [
                    'type' => static::getTableName(),
                    'locale' => $this->get('meta.locale')
                ],
            ],
            'authorities' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\AuthorityService',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [

                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale')
                ],
                'clearFields' => [
                    'locale' => $this->get('meta.locale')
                ]
            ],
            'locations' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\LocationService',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale')
                ],
                'clearFields' => [
                    'locale' => $this->get('meta.locale')
                ]
            ],
            'requirements' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\ServiceInformation',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'requirements',
                    'sort' => function ($position, $key, $value) {
                        return $position;
                    }
                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => 'requirements',
                ],
                'clearFields' => [
                    'type' => 'requirements',
                    'locale' => $this->get('meta.locale')
                ],
            ],
            'forms' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\ServiceInformation',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'forms',
                    'sort' => function ($position, $key, $value) {
                        return $position;
                    }
                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => 'forms',
                ],
                'clearFields' => [
                    'type' => 'forms',
                    'locale' => $this->get('meta.locale')
                ],
            ],
            'prerequisites' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\ServiceInformation',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'prerequisites',
                    'sort' => function ($position, $key, $value) {
                        return $position;
                    }
                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => 'prerequisites',
                ],
                'clearFields' => [
                    'type' => 'prerequisites',
                    'locale' => $this->get('meta.locale')
                ],
            ],
            'links' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\ServiceInformation',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'links',
                    'sort' => function ($position, $key, $value) {
                        return $position;
                    }
                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => 'links',
                ],
                'clearFields' => [
                    'type' => 'links',
                    'locale' => $this->get('meta.locale')
                ],
            ],
            'publications' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\ServiceInformation',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'publications',
                    'sort' => function ($position, $key, $value) {
                        return $position;
                    }
                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => 'publications',
                ],
                'clearFields' => [
                    'type' => 'publications',
                    'locale' => $this->get('meta.locale')
                ],
            ],
            'legal' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\ServiceInformation',
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'legal',
                    'sort' => function ($position, $key, $value) {
                        return $position;
                    }
                ],
                'deleteFields' => [
                    'service_id' => $this->get('id'),
                    'locale' => $this->get('meta.locale'),
                    'type' => 'legal',
                ],
                'clearFields' => [
                    'type' => 'legal',
                    'locale' => $this->get('meta.locale')
                ],
            ]
        ];
    }

    public function preSetup()
    {
        try {
            /*
            if (false === $this->get('meta.translated')) {
                $this->setStatus(static::STATUS_OLD);
                error_log(
                    'not translated service - (' . $this->get('id') . ' | ' .
                    $this->get('meta.locale') . ') - ' . $this->get('name')
                );
                return false;
            }
            else {
                error_log(
                    'translated service - (' . $this->get('id') . ' | ' .
                    $this->get('meta.locale') . ') - ' . $this->get('name')
                );
            }
            */
            $fields = $this->get(['id', 'meta.locale', 'meta.hash']);
            $fields[] = static::getTableName();

            if (is_array($fields[2])) {
                error_log(print_r($fields[2]));
            }
            #error_log(print_r($fields[2]));

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
            #print_r((array)$this->get(['meta.locale']));exit;
            return $this->deleteWith(
                ['locale' => $this->get('meta.locale')]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
