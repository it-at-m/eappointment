<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Link as Entity;
use \BO\Zmsentities\Collection\LinkList as Collection;

class Link extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Collection\LinkList
     */
    public function readByDepartmentId($departmentId)
    {
        $linkList = new Collection();
        $query = new Query\Link(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionDepartmentId($departmentId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $linkList->addEntity($entity);
                }
            }
        }
        return $linkList;
    }
}
