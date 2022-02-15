<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class TopicCluster extends Base
{
    protected $fieldMapping = [
        'id' => 'topic_id',
        'parent_id' => 'parent_id',
        'rank' => 'rank'
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['topic_id', 'parent_id'], array_values($this->get('id', 'parent_id')))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
