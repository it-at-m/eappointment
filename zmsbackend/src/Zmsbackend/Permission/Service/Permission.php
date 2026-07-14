<?php

namespace BO\Zmsbackend\Permission\Service;

use BO\Zmsentities\Collection\PermissionList as Collection;
use BO\Zmsentities\Permission as Entity;

class Permission extends \BO\Zmsbackend\Base
{
    public function readAllPermissions(string $order = 'ASC'): Collection
    {
        $list = new Collection();
        $query = new \BO\Zmsbackend\Permission\Repository\Permission(\BO\Zmsbackend\Query\Base::SELECT);
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
