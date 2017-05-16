<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Elastic;

use \BO\Dldb\Entity\Topic as Entity;
use \BO\Dldb\Collection\Topics as Collection;
use \BO\Dldb\File\Topic as Base;

/**
  *
  */
class Topic extends Base
{
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
