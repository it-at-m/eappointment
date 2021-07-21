<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Topic as Entity;
use \BO\Dldb\MySQL\Collection\Topics as Collection;
use \BO\Dldb\Elastic\Topic as Base;

/**
  *
  */
class Topic extends Base
{
    public function fetchList()
    {
        try {
            $sqlArgs = [$this->locale];
            $sql = 'SELECT data_json FROM topic WHERE locale = ?';

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Topic');

            $stm->execute($sqlArgs);
            $topics = $stm->fetchAll();

            $topiclist = new Collection();
            
            foreach ($topics as $topic) {
                $topiclist[$topic['id']] = $topic;
            }
            return $topiclist;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Entity
     */
    public function fetchPath($topic_path)
    {
        try {
            $sqlArgs = [$this->locale, (string)$topic_path];
            $sql = 'SELECT data_json FROM topic WHERE locale = ? AND path = ?';

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Topic');
            $stm->execute($sqlArgs);
            
            if (!$stm || ($stm && $stm->rowCount() == 0)) {
                return false;
            }
            $topic = $stm->fetch();
            return $topic;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Entity
     */
    public function fetchId($topicId)
    {
        try {
            $sqlArgs = [$this->locale, (int)$topicId];
            $sql = 'SELECT data_json FROM topic WHERE locale = ? AND id = ?';

            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Topic');
            $stm->execute($sqlArgs);
            
            if (!$stm || ($stm && $stm->rowCount() == 0)) {
                return false;
            }
            $topic = $stm->fetch();
            return $topic;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function readSearchResultList($query)
    {
        $boolquery = Helper::boolFilteredQuery();
        $searchquery = new \Elastica\Query\QueryString();
        if ('' === trim($query)) {
            $searchquery->setQuery('*');
        } else {
            $searchquery->setQuery($query);
        }
        $searchquery->setFields([
            'name^9',
            'keywords^5'
        ]);
        $boolquery->getQuery()->addShould($searchquery);
        $filter = null;
        $query = new \Elastica\Query\Filtered($boolquery, $filter);
        $resultList = $this->access()
            ->getIndex()
            ->getType('topic')
            ->search($query, 1000);
        $topicList = new Collection();
        foreach ($resultList as $result) {
            $topic = new Entity($result->getData());
            $topicList[$topic['id']] = $topic;
        }
        return $topicList;
    }
}
