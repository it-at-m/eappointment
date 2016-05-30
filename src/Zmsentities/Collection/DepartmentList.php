<?php
namespace BO\Zmsentities\Collection;

class DepartmentList extends Base
{
    public function addDepartment($department)
    {
        if ($department instanceof \BO\Zmsentities\Department) {
            $this[] = clone $department;
        }
        return $this;
    }
}
