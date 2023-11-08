<?php

namespace BO\Zmsdb\Query;

class Department extends Base implements MappingInterface
{
    const TABLE = 'behoerde';

    const QUERY_MAIL_UPDATE = '
        SET @tempEmailID = (SELECT emailID from email WHERE BehoerdenID=:departmentId);
        REPLACE INTO
            email
        SET
            emailID=@tempEmailID,
            BehoerdenID=:departmentId,
            absenderadresse=:email,
            serveradresse="localhost",
            send_reminder=:sendEmailReminderEnabled,
            send_reminder_minutes_before=:sendEmailReminderMinutesBefore
    ';

    const QUERY_NOTIFICATIONS_UPDATE = '

    SET @tempSMSId = (SELECT smsID from sms WHERE BehoerdenID=:departmentId);
    REPLACE INTO
        sms
    SET
        smsID=@tempSMSId,
        BehoerdenID=:departmentId,
        enabled=:enabled,
        Absender=:identification,
        internetbestaetigung=:sendConfirmationEnabled,
        interneterinnerung=:sendReminderEnabled

    ';

    const QUERY_MAIL_INSERT = '
        REPLACE INTO
            email
        SET
            BehoerdenID=?,
            serveradresse="localhost",
            absenderadresse=?,
            send_reminder=?,
            send_reminder_minutes_before=?
    ';

    const QUERY_NOTIFICATIONS_INSERT = '
        REPLACE INTO
            sms
        SET
            BehoerdenID=?,
            enabled=?,
            Absender=?,
            internetbestaetigung=?,
            interneterinnerung=?
    ';

    const QUERY_MAIL_DELETE = '
        DELETE FROM email WHERE BehoerdenID=?
    ';

    const QUERY_NOTIFICATIONS_DELETE = '
        DELETE FROM sms WHERE BehoerdenID=?
    ';

    public function getEntityMapping()
    {
        return [
            'contact__city' => self::expression('TRIM(" " FROM SUBSTRING_INDEX(`department`.`Adresse`, " ", -1))'),
            'contact__street' => 'department.Adresse',
            /*
            'contact__streetNumber' => self::expression(
                'TRIM("," FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`department`.`Adresse`, ",", 1), " ", -1))'
            ),
            'contact__postalCode' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`department`.`Adresse`, " ", -2), " ", 1))'
            ),
            'contact__region' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`department`.`Adresse`, " ", -1))'
            ),
            */
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'department.Ansprechpartner',
            'email' => 'department_email.absenderadresse',
            'sendEmailReminderEnabled' => 'department_email.send_reminder',
            'sendEmailReminderMinutesBefore' => 'department_email.send_reminder_minutes_before',
            'id' => 'department.BehoerdenID',
            'name' => 'department.Name',
            'preferences__notifications__enabled' => 'department_sms.enabled',
            'preferences__notifications__identification' => 'department_sms.Absender',
            'preferences__notifications__sendConfirmationEnabled' => 'department_sms.internetbestaetigung',
            'preferences__notifications__sendReminderEnabled' => 'department_sms.interneterinnerung'
        ];
    }

    public function addRequiredJoins()
    {
        $this->leftJoin(
            new Alias('email', 'department_email'),
            'department.BehoerdenID',
            '=',
            'department_email.BehoerdenID'
        );
        $this->leftJoin(
            new Alias('sms', 'department_sms'),
            'department.BehoerdenID',
            '=',
            'department_sms.BehoerdenID'
        );
    }

    public function addConditionScopeId($scopeId)
    {
        $this->leftJoin(
            new Alias('standort', 'scope_department'),
            'scope_department.BehoerdenID',
            '=',
            'department.BehoerdenID'
        );
        $this->query->where('scope_department.StandortID', '=', $scopeId);
        return $this;
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

    public function reverseEntityMapping(\BO\Zmsentities\Department $entity, $parentId = null)
    {
        $data = array();
        if (null !== $parentId) {
            $data['OrganisationsID'] = $parentId;
        }
        $data['Adresse'] = (isset($entity->contact['street'])) ?$entity->contact['street'] : '';
        $data['Name'] = $entity->name;
        $data['Ansprechpartner'] = $entity->getContactPerson();
        $data = array_filter(
            $data,
            function ($value) {
                return ($value !== null && $value !== false);
            }
        );
        return $data;
    }
}
