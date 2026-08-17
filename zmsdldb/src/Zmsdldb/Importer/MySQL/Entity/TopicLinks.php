<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class TopicLinks extends Base
{
    protected array $fieldMapping = [
        'topic_id' => 'topic_id',
        'name' => 'name',
        'locale' => 'locale',
        'rank' => 'rank',
        'link' => 'url',
        'search' => 'search',
        'highlight' => 'highlight',
        #'meta.keywords' => 'keywords',
        #'meta.titles' => 'titles',
        'meta' => 'meta_json',
        '__RAW__' => 'data_json'
    ];

    #[\Override]
    public function postSetupFields(): void
    {
        $searchValues = [$this->get('name')];
        /*
        if (array_key_exists('titles', ($this->fields['meta'] ?? [])) && !empty($this->fields['meta']['titles'])) {
            $titels = $this->fields['meta']['titles'];
            if (is_string($this->fields['meta']['titles'])) {
                $titels = explode(',', $this->fields['meta']['titles']);
            }
            $titels = array_filter($titels);
            array_push($searchValues, ...$titels);
        }
        */

        $keywords = $this->get('meta.keywords');
        if (!empty($keywords)) {
            if (is_string($keywords)) {
                $keywords = explode(',', $keywords);
            }
            $keywords = array_filter($keywords);
            array_push($searchValues, ...$keywords);
        }

        $this->fields['search'] = implode(', ', $searchValues);
    }

    #[\Override]
    public function clearEntity(array $addWhere = []): void
    {
        try {
            $this->deleteWith(
                ['locale' => $this->get('meta.locale')]
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
                array_combine(['topic_id', 'locale'], array_values($this->get('topic_id', 'locale')))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
