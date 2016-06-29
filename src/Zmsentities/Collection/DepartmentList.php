<?php
namespace BO\Zmsentities\Collection;

class DepartmentList extends Base
{
    public function addDepartment($department)
    {
        $this[] = clone $department;
        return $this;
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $entity) {
            if ($entityId == $entity->id) {
                return true;
            }
        }
        return false;
    }
}
