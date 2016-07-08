<?php
namespace BO\Zmsentities\Collection;

class OrganisationList extends Base
{
    public function getByDepartmentId($departmentId)
    {
        $organisationList = new self();
        foreach ($this as $entity) {
            $organisation = new \BO\Zmsentities\Organisation($entity);
            if ($organisation->hasDepartment($departmentId)) {
                $organisationList->addEntity($organisation);
            }
        }
        return $organisationList;
    }
}
