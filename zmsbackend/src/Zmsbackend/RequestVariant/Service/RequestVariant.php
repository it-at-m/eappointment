<?php

namespace BO\Zmsbackend\RequestVariant\Service;

class RequestVariant extends \BO\Zmsbackend\Base
{
    public function readAll(): array
    {
        $q = new \BO\Zmsbackend\RequestVariant\Repository\RequestVariant(\BO\Zmsbackend\Query\Base::SELECT);
        $q->addEntityMapping()->orderByName();
        $stmt = $this->fetchStatement($q);
        $out = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $out[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }
        return $out;
    }
}
