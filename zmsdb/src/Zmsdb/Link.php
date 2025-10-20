<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Link as Entity;
use BO\Zmsentities\Collection\LinkList as Collection;

class Link extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Collection\LinkList
     */
    public function readByDepartmentId($departmentId, $disableCache = false)
    {
        $cacheKey = "linksReadByDepartmentId-$departmentId";

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            return App::$cache->get($cacheKey);
        }

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

        if (App::$cache) {
            App::$cache->set($cacheKey, $linkList);
        }

        return $linkList;
    }

    /**
     * write a link
     *
     * @param
     * entity,
     * organisationId
     *
     * @return Entity
     */
    public function writeEntity(Entity $entity, $departmentId)
    {
        $query = new Query\Link(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $departmentId);
        $query->addValues($values);

        if (App::$cache) {
            App::$cache->delete("linksReadByDepartmentId-$departmentId");
        }

        return $this->writeItem($query);
    }

    public function deleteEntity($itemId)
    {
        $query = new Query\Link(Query\Base::DELETE);
        $query->addConditionLinkId($itemId);
        return ($this->deleteItem($query));
    }
}
