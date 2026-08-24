<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

use Error;

class Service extends Base
{
    protected array $fieldMapping = [
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
            /*
            'description' => [
                'class' => Search::class,
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
                'class' => AuthorityService::class,
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
                'class' => LocationService::class,
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
                'class' => ServiceInformation::class,
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'requirements',
                    'sort' => static function (int $position): int {
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
                'class' => ServiceInformation::class,
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'forms',
                    'sort' => static function (int $position): int {
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
                'class' => ServiceInformation::class,
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'prerequisites',
                    'sort' => static function (int $position): int {
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
                'class' => ServiceInformation::class,
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'links',
                    'sort' => static function (int $position): int {
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
                'class' => ServiceInformation::class,
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'publications',
                    'sort' => static function (int $position): int {
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
                'class' => ServiceInformation::class,
                'neededFields' => ['id' => 'service_id', 'meta.locale' => 'locale'],
                'addFields' => [
                    'type' => 'legal',
                    'sort' => static function (int $position): int {
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
            #print_r((array)$this->get(['meta.locale']));exit;
            $this->deleteWith(
                ['locale' => $this->get('meta.locale')]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
