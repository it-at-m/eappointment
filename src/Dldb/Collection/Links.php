<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Links extends Base
{
    public function toSearchResultData()
    {
        $list = array();
        foreach ($this as $link) {
            $list[] = array(
                'type' => 'Link',
                'name' => $link['name'],
                'link' => $link['link']
            );
        }
        return $list;
    }
}
