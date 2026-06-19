<?php

namespace BO\Zmsbackend\Permission\Repository;

class Permission extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var string TABLE mysql table reference
     */
    const TABLE = 'permission';

    #[\Override]
    public function getEntityMapping()
    {
        return [
            'id' => 'permission.id',
            'name' => 'permission.name',
            'description' => 'permission.description',
        ];
    }

    public function addOrderByName(string $order = 'ASC'): self
    {
        $this->query->orderBy('permission.name', $order);
        return $this;
    }

    public function addConditionName(string $name): self
    {
        $this->query->where('permission.name', '=', $name);
        return $this;
    }

    public function addConditionNames(array $names): self
    {
        if ($names === []) {
            throw new \InvalidArgumentException('Argument $names must not be empty.');
        }
        $this->query->whereIn('permission.name', $names);
        return $this;
    }
}
