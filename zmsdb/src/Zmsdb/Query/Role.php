<?php

namespace BO\Zmsdb\Query;

class Role extends Base implements MappingInterface
{
    /**
     * @var string TABLE mysql table reference
     */
    const TABLE = 'role';

    public function getEntityMapping()
    {
        return [
            'id' => 'role.id',
            'name' => 'role.name',
            'description' => 'role.description',
        ];
    }

    public function addConditionName(string $name): self
    {
        $this->query->where('role.name', '=', $name);
        return $this;
    }

    public function addConditionNames(array $names): self
    {
        if (!empty($names)) {
            $this->query->whereIn('role.name', $names);
        }
        return $this;
    }
}
