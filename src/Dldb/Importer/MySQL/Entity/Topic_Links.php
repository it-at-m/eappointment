<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Topic_Links extends Base
{
    protected $fieldMapping = [
        'topic_id' => 'topic_id',
        'name' => 'name',
        'locale' => 'locale',
        'rank' => 'rank',
        'link' => 'url',
        'highlight' => 'highlight',
        'meta' => 'meta_json'
    ];

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['topic_id', 'locale'], array_values($this->get('topic_id', 'locale')))
            );
        }
        catch (\Exception $e) {
            throw $e;
        }
    }
}