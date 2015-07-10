<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Topics extends Base
{
    public static function setSidebar()
    {
        $list = \App::$repository->fetchTopicList();
        $sidebar = array();
        foreach ($list as $key => $topic) {
            if ($topic['relation']['navi'] == 1) {
                $sidebar[] = array(
                    'id' => $topic['id'],
                    'name' => $topic['name'],
                    'path' => $topic['path'],
                    'rank' => $topic['relation']['rank'],
                    'root' => $topic['relation']['root']
                );
            }
        }
        return $sidebar;
    }
}
