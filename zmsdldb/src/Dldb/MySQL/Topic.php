<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\MySQL;

use BO\Dldb\MySQL\Entity\Topic as Entity;
use BO\Dldb\MySQL\Collection\Topics as Collection;
use BO\Dldb\Elastic\Topic as Base;

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

            $topiclist = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($topiclist) {
                $topic = new \BO\Dldb\MySQL\Entity\Topic();
                $topic->offsetSet('data_json', $data_json);

                $topiclist[$topic['id']] = $topic;
            });

            return $topiclist;
        } catch (\Exception $e) {
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
            $stm->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Topic');
            $stm->execute($sqlArgs);

            if (!$stm || ($stm && $stm->rowCount() == 0)) {
                return false;
            }
            $topic = $stm->fetch();
            return $topic;
        } catch (\Exception $e) {
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
            $stm->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Topic');
            $stm->execute($sqlArgs);

            if (!$stm || ($stm && $stm->rowCount() == 0)) {
                return false;
            }
            $topic = $stm->fetch();
            return $topic;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function readSearchResultList($query)
    {
        try {
            #$query = '+' . implode(' +', explode(' ', $query));
            $sqlArgs = [$this->locale, $this->locale, $query];
            $sql = "SELECT t.data_json 
            FROM search AS se
            LEFT JOIN topic AS t ON t.id = se.object_id AND t.locale = ?
            WHERE 
                se.locale = ? AND MATCH (search_value) AGAINST (? IN BOOLEAN MODE)
                AND (search_type IN ('name', 'keywords', 'titles')) AND entity_type='topic'
             GROUP BY se.object_id
            ";
            /*
            if (!empty($service_csv)) {
                $ids = explode(',', $service_csv);
                $qm = array_fill(0, count($ids), '?');
                $sql .= ' AND se.object_id IN (' . implode(', ', $qm) . ')';
                array_push($sqlArgs, ...$ids);
            }*/
            #print_r($sql);exit;

            $topiclist = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($topiclist) {
                $topic = new \BO\Dldb\MySQL\Entity\Topic();
                $topic->offsetSet('data_json', $data_json);

                $topiclist[$topic['id']] = $topic;
            });

            return $topiclist;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
