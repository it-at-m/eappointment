<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Link as Entity;
use \BO\Dldb\MySQL\Collection\Links as Collection;
use \BO\Dldb\Elastic\Link as Base;

/**
 */
class Link extends Base
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
            'name^3',
            'meta.titles^5',
            'meta.keywords^9'
        ]);
        $boolquery->getQuery()->addShould($searchquery);
        $resultList = $this->access()
            ->getIndex()
            ->getType('links')
            ->search($boolquery, 1000);
        $linkList = new Collection();
        foreach ($resultList as $result) {
            $link = new Entity($result->getData());
            if (false === array_key_exists($link['link'], $linkList)) {
                $linkList[$link['link']] = $link;
            }
        }
        return $linkList;
    }
}
