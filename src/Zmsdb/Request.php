<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;

class Request extends Base
{
    public function readEntity($source, $requestId)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionRequestId($requestId);
        return $this->fetchOne($query, new Entity());
    }
}
