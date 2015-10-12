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


    /**
     * @return \BO\Dldb\Collection\Authorities
     */
    public function searchAll($querystring)
    {
        $topic = new Entity();
        $topic['relation']['locations'] = $this->access()->fromLocation()->searchList($querystring);
        $topic['relation']['services'] = $this->access()->fromService()->searchList($querystring);
        //var_dump($topic);
        return $topic->getServiceLocationLinkList();
    }
}
