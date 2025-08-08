<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\MySQL;

use BO\Zmsdldb\MySQL\Collection\Links as Collection;
use BO\Zmsdldb\Elastic\Link as Base;

/**
 */
class Link extends Base
{
    public function readSearchResultList($query)
    {
        try {
            #$query = '+' . implode(' +', explode(' ', $query));
            $sqlArgs = [$this->locale, $query];
            $sql = "SELECT tl.data_json 
            FROM topic_links AS tl
            WHERE 
            tl.locale = ? AND MATCH (tl.search) AGAINST (? IN BOOLEAN MODE)
            ";

            $linklist = new Collection();

            $stm = $this->access()->prepare($sql);
            $stm->execute($sqlArgs);
            $stm->fetchAll(\PDO::FETCH_FUNC, function ($data_json) use ($linklist) {
                $link = new \BO\Zmsdldb\MySQL\Entity\Link();
                $link->offsetSet('data_json', $data_json);
                $linklist[$link['link']] = $link;
            });

            return $linklist;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
