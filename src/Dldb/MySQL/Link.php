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
        try {
            $sqlArgs = [$this->locale, $query];
            $sql = "SELECT tl.data_json 
            FROM topic_links AS tl
            WHERE 
            tl.locale = ? AND MATCH (tl.search) AGAINST (? IN NATURAL LANGUAGE MODE)
            ";
           
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\\BO\\Dldb\\MySQL\\Entity\\Link');
            $stm->execute($sqlArgs);
            
            $links = $stm->fetchAll();
            
            $linklist = new Collection();
            
            foreach ($links as $link) {
                $linklist[$link['link']] = $link;
            }
            
            return $linklist;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
