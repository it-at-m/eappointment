<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Collection\PermissionList as Collection;
use BO\Zmsentities\Permission as Entity;

class Permission extends Base
{
    public function readAllPermissions(string $order = 'ASC'): Collection
    {
        $list = new Collection();
        $query = new Query\Permission(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences(0)
            ->addOrderByName($order);
        $result = $this->fetchList($query, new Entity());
        foreach ($result as $entity) {
            $list->addEntity($entity);
        }
        return $list;
    }
}
