<?php

namespace BO\Zmsdb\Query;

class Department extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'behoerde';

    public function getEntityMapping()
    {
        return [
            'contact__city' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`department`.`Adresse`, " ", -1))'
            ),
            'contact__street' => self::expression('SUBSTRING_INDEX(`department`.`Adresse`, " ", 1)'),
            'contact__streetNumber' => self::expression(
                'TRIM("," FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`department`.`Adresse`, ",", 1), " ", -1))'
            ),
            'contact__postalCode' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`department`.`Adresse`, " ", -2), " ", 1))'
            ),
            'contact__region' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`department`.`Adresse`, " ", -1))'
            ),
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'department.Ansprechpartner',
            'email' => 'department_email.absenderadresse',
            'id' => 'department.BehoerdenID',
            'name' => 'department.Name',
            'preferences__notifications__enabled' => 'department_sms.enabled',
            'preferences__notifications__identification' => 'department_sms.Absender',
            'preferences__notifications__sendConfirmationEnabled' => 'department_sms.internetbestaetigung',
            'preferences__notifications__sendReminderEnabled' => 'department_sms.interneterinnerung',
        ];
    }

    public function addRequiredJoins()
    {
        $this->query->leftJoin(
            new Alias('email', 'department_email'),
            'department.BehoerdenID',
            '=',
            'department_email.BehoerdenID'
        );
        $this->query->leftJoin(
            new Alias('sms', 'department_sms'),
            'department.BehoerdenID',
            '=',
            'department_sms.BehoerdenID'
        );
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->query->where('department.BehoerdenID', '=', $departmentId);
        return $this;
    }

    public function addConditionOrganisationId($organisationId)
    {
        $this->query->where('department.OrganisationsID', '=', $organisationId);
        return $this;
    }
}
