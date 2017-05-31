<?php

namespace BO\Zmsdb\Query;

class Useraccount extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'nutzer';
    const TABLE_ASSIGNMENT = 'nutzerzuordnung';

    const QUERY_READ_ID_BY_USERNAME = '
        SELECT user.`NutzerID` AS id
        FROM '. self::TABLE .' user
        WHERE
            user.`Name`=?
    ';

    const QUERY_WRITE_ASSIGNED_DEPARTMENTS = '
        REPLACE INTO
            '. self::TABLE_ASSIGNMENT .'
        SET
            nutzerid=?,
            behoerdenid=?
    ';

    const QUERY_DELETE_ASSIGNED_DEPARTMENTS = '
        DELETE FROM
            '. self::TABLE_ASSIGNMENT .'
        WHERE
            nutzerid=?
    ';

    const QUERY_READ_SUPERUSER_DEPARTMENTS = '
        SELECT behoerde.`BehoerdenID` AS id,
            organisation.Organisationsname as organisation__name
        FROM behoerde
        LEFT JOIN organisation USING(OrganisationsID)
        ORDER BY organisation.Organisationsname, behoerde.Name
    ';

    const QUERY_READ_ASSIGNED_DEPARTMENTS = '
        SELECT userAssignment.`behoerdenid` AS id,
            organisation.Organisationsname as organisation__name
        FROM '. self::TABLE_ASSIGNMENT .' userAssignment
        LEFT JOIN '. self::TABLE .' userAccount ON userAccount.Name = :userAccountName
        LEFT JOIN behoerde ON userAssignment.behoerdenid = behoerde.BehoerdenID
        LEFT JOIN organisation USING(OrganisationsID)
        WHERE
            userAccount.`NutzerID` = userAssignment.`nutzerid`
        ORDER BY organisation.Organisationsname, behoerde.Name
    ';

    public function getEntityMapping()
    {
        return [
            'id' => 'userAccount.Name',
            'lastLogin' => 'userAccount.Datum',
            'rights__superuser' => self::expression('`userAccount`.`Berechtigung` = 90'),
            'rights__organisation' => self::expression('`userAccount`.`Berechtigung` >= 70'),
            'rights__department' => self::expression('`userAccount`.`Berechtigung` >= 50'),
            'rights__cluster' => self::expression('`userAccount`.`Berechtigung` >= 40'),
            'rights__useraccount' => self::expression('`userAccount`.`Berechtigung` >= 40'),
            'rights__scope' => self::expression('`userAccount`.`Berechtigung` >= 30'),
            'rights__availability' => self::expression('`userAccount`.`Berechtigung` >= 20'),
            'rights__ticketprinter' => self::expression('`userAccount`.`Berechtigung` >= 15'),
            'rights__sms' => self::expression('`userAccount`.`Berechtigung` >= 10'),
            'rights__basic' => self::expression('`userAccount`.`Berechtigung` >= 0'),
        ];
    }

    public function addConditionLoginName($loginName)
    {
        $this->query->where('userAccount.Name', '=', $loginName);
        return $this;
    }

    public function addConditionUserId($userId)
    {
        $this->query->where('userAccount.NutzerID', '=', $userId);
        return $this;
    }



    public function addConditionPassword($password)
    {
        $this->query->where('userAccount.Passworthash', '=', md5($password));
        return $this;
    }

    public function addConditionXauthKey($xAuthKey)
    {
        $this->query->where('userAccount.SessionID', '=', $xAuthKey);
        return $this;
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->leftJoin(
            new Alias(static::TABLE_ASSIGNMENT, 'useraccount_department'),
            'userAccount.NutzerID',
            '=',
            'useraccount_department.nutzerid'
        );
        $this->query->where('useraccount_department.behoerdenid', '=', $departmentId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Useraccount $entity)
    {
        $data = array();
        $data['Name'] = $entity->id;
        $data['Passworthash'] = (isset($entity->password)) ? md5($entity->password) : null;
        $data['Berechtigung'] = $entity->getRightsLevel();
        //default values because of strict mode
        $data['notrufinitiierung'] = 0;
        $data['notrufantwort'] = 0;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }

    public function postProcess($data)
    {
        $data["lastLogin"] = ('0000-00-00' != $data["lastLogin"]) ?
            strtotime($data["lastLogin"]) :
            null;
        return $data;
    }
}
