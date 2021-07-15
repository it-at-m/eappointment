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

    protected function setupMapping() {
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
            'links' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Topic_Links',
                'neededFields' => ['id' => 'topic_id', 'meta.locale' => 'locale'],
                'addFields' => [],
                'delete' => false,
                'deleteFunction' => function(\BO\Dldb\Importer\MySQL\Entity\Topic $topic) {
                    $id = $topic->get('id');
                    try {
                        $sql = "DELETE FROM " . \BO\Dldb\Importer\MySQL\Entity\Topic_Links::getTableName() . ' WHERE topic_id = ?';
                        $stm = $topic->getPDOAccess()->prepare($sql);
            
                        $stm->execute([$id]);
                        if ($stm && 0 < $stm->rowCount()) {
                            return true;
                        }
                        return false;
                    }
                    catch (\Exception $e) {
                        throw $e;
                    }
                },
                'clearFields' => [
                    'locale' => $this->get('meta.locale')
                ]
            ],
            'relation.services' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Topic_Service',
                'neededFields' => ['id' => 'topic_id'],
                'addFields' => [],
                'deleteFunction' => function(\BO\Dldb\Importer\MySQL\Entity\Topic $topic) {
                    $id = $topic->get('id');
                    try {
                        $sql = "DELETE FROM " . \BO\Dldb\Importer\MySQL\Entity\Topic_Service::getTableName() . ' WHERE topic_id = ?';
                        $stm = $topic->getPDOAccess()->prepare($sql);
            
                        $stm->execute([$id]);
                        if ($stm && 0 < $stm->rowCount()) {
                            return true;
                        }
                        return false;
                    }
                    catch (\Exception $e) {
                        throw $e;
                    }
                }
            ],
            'relation.childs' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Topic_Cluster',
                'neededFields' => ['id' => 'parent_id'],
                'addFields' => [],
                'deleteFunction' => function(\BO\Dldb\Importer\MySQL\Entity\Topic $topic) {
                    $id = $topic->get('id');
                    try {
                        $sql = "DELETE FROM " . \BO\Dldb\Importer\MySQL\Entity\Topic_Cluster::getTableName() . ' WHERE parent_id = ?';
                        $stm = $topic->getPDOAccess()->prepare($sql);
            
                        $stm->execute([$id]);
                        if ($stm && 0 < $stm->rowCount()) {
                            return true;
                        }
                        return false;
                    }
                    catch (\Exception $e) {
                        throw $e;
                    }
                }
            ]
        ];
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
                ['locale' => $this->get('locale')]
            );
        }
        catch (\Exception $e) {
            throw $e;
        }
    }
}