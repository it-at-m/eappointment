<?php

namespace BO\Dldb\Helper\MySQL;

use BO\Dldb\MySQLAccess;
use BO\Dldb\Entity\SearchResult;
use BO\Dldb\Collection\SearchResults;
use Error;

class Search
{
    protected $objectTypesClasses = [
        'service' => '\\BO\\Dldb\\MySQL\\Entity\\Service',
        'location' => '\\BO\\Dldb\\MySQL\\Entity\\Location',
        'topic' => '\\BO\\Dldb\\MySQL\\Entity\\Topic',
        #'link' => '\\BO\\Dldb\\MySQL\\Entity\\Link'
    ];

    protected $searchTypes = [];
    protected $entityTypes = [];
    protected $mysqlAccess = null;

    protected $entityIds = [];

    public function __construct(MySQLAccess $mysqlAccess, array $entityTypes = [], array $searchTypes = [])
    {
        $this->entityTypes = $entityTypes;
        $this->searchTypes = $searchTypes;
        $this->mysqlAccess = $mysqlAccess;
    }

    public function fetchSearchRow($object_id, $locale, $entity_type)
    {
        if (!isset($this->entityIds[$entity_type])) {
            $this->entityIds[$entity_type] = [];
        }
        if (!isset($this->entityIds[$entity_type][$locale])) {
            $this->entityIds[$entity_type][$locale] = [];
        }
        if (!isset($this->entityIds[$entity_type][$locale][$object_id])) {
            $this->entityIds[$entity_type][$locale][$object_id] = $object_id;
        }
    }

    protected function getSearchResults(string $query): SearchResults
    {
        try {
            $resultList = new SearchResults();

            foreach ($this->objectTypesClasses as $type => $entityClass) {
                if (isset($this->entityIds[$type])) {
                    $sql = "SELECT data_json FROM " . $type . " WHERE ";
                    $where = [];

                    foreach ($this->entityIds[$type] as $locale => $ids) {
                        $where[] = "(locale = '" . $locale . "' AND id IN (" . implode(',', $ids) . "))";
                    }
                    $sql .= implode(' OR ', $where);

                    $stm = $this->mysqlAccess->prepare($sql);
                    $stm->execute();
                    $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($entityClass, $resultList) {
                        $entity = new $entityClass();
                        $entity->offsetSet('data_json', $data_json);

                        $resultList[] = SearchResult::create($entity);
                    });
                }
            }
            $links = $this->mysqlAccess->fromLink()->readSearchResultList($query);

            foreach ($links as $link) {
                $resultList[] = SearchResult::create($link);
            }
            return $resultList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function execute(string $query): SearchResults
    {
        try {
            $query = '+' . implode(' +', explode(' ', $query));

            $sqlArgs = [$query];
            $sql = "SELECT 
                object_id, locale, entity_type 
            FROM 
                search 
            WHERE
                MATCH (search_value) AGAINST (? IN BOOLEAN MODE)
            ";

            if (!empty($this->entityTypes)) {
                $sql .= " AND entity_type ";
                if (1 == count($this->entityTypes)) {
                    $sql .= " = ?";
                    $sqlArgs[] = current($this->entityTypes);
                } else {
                    $questionMarks = array_fill(0, count($this->entityTypes), '?');
                    $sql .= ' IN (' . implode(', ', $questionMarks) . ')';
                    array_push($sqlArgs, ...$this->entityTypes);
                }
            }
            $sql .= ' GROUP BY object_id, entity_type, locale';

            $stm = $this->mysqlAccess->prepare($sql);

            $stm->execute($sqlArgs);

            $stm->fetchAll(\PDO::FETCH_FUNC, [$this, 'fetchSearchRow']);

            $resultList = $this->getSearchResults($query);

            return $resultList;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
