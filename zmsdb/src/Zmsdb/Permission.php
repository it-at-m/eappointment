<?php

namespace BO\Zmsdb;

class Permission extends Base
{
    /**
     * Read all permissions with id, name, and description.
     *
     * @return array<int, array{id:int,name:string,description:?string}>
     */
    public function readAll(): array
    {
        $sql = 'SELECT id, name, description FROM permission ORDER BY name';
        $rows = $this->getReader()->fetchAll($sql, []);

        if (!is_array($rows) || empty($rows)) {
            return [];
        }

        return $rows;
    }
}
