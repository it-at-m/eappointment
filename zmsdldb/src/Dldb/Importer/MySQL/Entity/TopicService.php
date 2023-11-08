<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class TopicService extends Base
{
    protected $fieldMapping = [
        'id' => 'service_id',
        'topic_id' => 'topic_id',
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['topic_id', 'service_id'],
                    array_values($this->get('topic_id', 'id'))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
