<?php

namespace BO\Zmsdb;

class RequestVariant extends Base
{
    public function readAll(): array
    {
        $q = new Query\RequestVariant(Query\Base::SELECT);
        $q->addEntityMapping()->orderByName();
        $stmt = $this->fetchStatement($q);
        $out = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $out[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }
        return $out;
    }
}
