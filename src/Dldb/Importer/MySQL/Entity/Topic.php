<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Topic extends Base
{
    protected $fieldMapping = [
        'id' => 'id',
        'name' => 'name',
        'meta.locale' => 'locale',
        'path' => 'path',
        'relation.navi' => 'navi',
        'relation.root' => 'root',
        'relation.rank' => 'rank',
        '__RAW__' => 'data_json'
    ];

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
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
                'multiple' => false,
                'clearFields' => [
                    'type' => static::getTableName(),
                    'locale' => $this->get('meta.locale')
                ],
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
            'meta.titles' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
                'neededFields' => [
                    'id' => 'object_id',
                    'meta.locale' => 'locale',
                    'meta.titles' => 'search_value'
                ],
                'addFields' => [
                    'entity_type' => static::getTableName(),
                    'search_type' => 'titles'
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('locale'),
                    'entity_type' => static::getTableName()
                ],
                'multiple' => false,
                'clearFields' => [
                    'entity_type' => static::getTableName(),
                    'locale' => $this->get('locale')
                ],
                'selfAsArray' => true
            ],
            'links' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\TopicLinks',
                'neededFields' => ['id' => 'topic_id', 'meta.locale' => 'locale'],
                'addFields' => ['locale' => $this->get('meta.locale')],
                'delete' => false,
                'deleteFunction' => function (\BO\Dldb\Importer\MySQL\Entity\Topic $topic) {
                    return static::deleteReferencesFn(
                        $topic,
                        \BO\Dldb\Importer\MySQL\Entity\TopicLinks::getTableName(),
                        'topic_id'
                    );
                },
                'clearFields' => [
                    'locale' => $this->get('meta.locale')
                ]
            ],
            'relation.services' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\TopicService',
                'neededFields' => ['id' => 'topic_id'],
                'addFields' => [],
                'deleteFunction' => function (\BO\Dldb\Importer\MySQL\Entity\Topic $topic) {
                    return static::deleteReferencesFn(
                        $topic,
                        \BO\Dldb\Importer\MySQL\Entity\TopicService::getTableName(),
                        'topic_id'
                    );
                }
            ],
            'relation.childs' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\TopicCluster',
                'neededFields' => ['id' => 'parent_id'],
                'addFields' => [],
                'deleteFunction' => function (\BO\Dldb\Importer\MySQL\Entity\Topic $topic) {
                    return static::deleteReferencesFn(
                        $topic,
                        \BO\Dldb\Importer\MySQL\Entity\TopicCluster::getTableName(),
                        'parent_id'
                    );
                }
            ]
        ];
    }

    protected static function deleteReferencesFn(
        \BO\Dldb\Importer\MySQL\Entity\Topic $topic,
        string $tableName,
        string $whereField
    ) {
        $topicId = $topic->get('id');
        try {
            $sql = "DELETE FROM " . $tableName . " WHERE " . $whereField . " = ?";
            $stm = $topic->getPDOAccess()->prepare($sql);

            $stm->execute([$topicId]);
            if ($stm && 0 < $stm->rowCount()) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
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

    public function clearEntity(array $addWhere = []) : bool
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
