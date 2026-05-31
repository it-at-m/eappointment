<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class TopicCluster extends Base
{
    /**
     * @var string[]
     *
     * @psalm-var array{id: 'topic_id', parent_id: 'parent_id', rank: 'rank'}
     */
    protected array $fieldMapping = [
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
