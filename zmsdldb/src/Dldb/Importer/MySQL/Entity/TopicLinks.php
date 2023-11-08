<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class TopicLinks extends Base
{
    protected $fieldMapping = [
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

    public function postSetupFields()
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
    
    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['topic_id', 'locale'], array_values($this->get('topic_id', 'locale')))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function postSave(\PDOStatement $stm, Base $entity)
    {
        return true;
        try {
            if ($stm && 0 < $stm->rowCount()) {
                #$lastInsertId = $pdoConnection->lastInsertId();

                $sql = 'REPLACE INTO ' . static::getTableName() . ' ';
                $sql .= '(`' . implode('`, `', array_keys($this->fields)) . '`) ';
                
                $questionMarks = array_fill(0, count($this->fields), '?');
                $sql .= 'VALUES (' . implode(', ', $questionMarks) . ') ';

                #print_r($sql . \PHP_EOL) ;
                $stm = $this->getPDOAccess()->prepare($sql);

                $stm->execute(array_values($this->fields));

                return true;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
