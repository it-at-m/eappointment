<?php

namespace BO\Zmsentities;

class Organisation extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "organisation.json";

    public function hasDepartment($departmentId)
    {
        $hasDepartment = false;
        foreach ($this->departments as $department) {
            if ($departmentId == $department['id']) {
                $hasDepartment = true;
            }
        }
        return $hasDepartment;
    }

    public function getPreference($index)
    {
        return $this->toProperty()->preferences->$index->get();
    }
}
