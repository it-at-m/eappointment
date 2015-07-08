<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Topics extends Base
{
	public function getSidebarTopics()
	{
		foreach($this as $key => $topic){			
        	if($topic['relation']['navi'] == 1){   
        		$list[] = array(
        			'id' => $topic['id'],
        			'name' => $topic['name'],
        			'path' => $topic['path'],
        			'rank' => $topic['relation']['rank'],
        			'root' => $topic['relation']['root']
        		);
        	}
        }       
		return $list;
	}
}
