<?php
namespace BO\Zmsentities\Collection;

class DepartmentList extends Base
{
    public function addDepartment($department)
    {
        $this[] = clone $department;
        return $this;
    }
}
