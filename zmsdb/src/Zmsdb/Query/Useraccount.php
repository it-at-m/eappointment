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
        FROM ' . self::TABLE . ' user
        WHERE
            user.`Name`=?
    ';

    const QUERY_WRITE_ASSIGNED_DEPARTMENTS = '
        REPLACE INTO
            ' . self::TABLE_ASSIGNMENT . '
        SET
            nutzerid=?,
            behoerdenid=?
    ';

    const QUERY_DELETE_ASSIGNED_DEPARTMENTS = '
        DELETE FROM
            ' . self::TABLE_ASSIGNMENT . '
        WHERE
            nutzerid=?
        ORDER BY behoerdenid
    ';

    const QUERY_READ_SUPERUSER_DEPARTMENTS = '
        SELECT behoerde.`BehoerdenID` AS id,
            organisation.Organisationsname as organisation__name
        FROM ' . Department::TABLE . '
        LEFT JOIN ' . Organisation::TABLE . ' USING(OrganisationsID)
        ORDER BY organisation.Organisationsname, behoerde.Name
    ';

    const QUERY_READ_ASSIGNED_DEPARTMENTS = '
        SELECT userAssignment.`behoerdenid` AS id,
            organisation.Organisationsname as organisation__name
        FROM ' . self::TABLE_ASSIGNMENT . ' userAssignment
        LEFT JOIN ' . self::TABLE . ' useraccount ON useraccount.Name = :useraccountName
        LEFT JOIN ' . Department::TABLE . ' ON userAssignment.behoerdenid = behoerde.BehoerdenID
        LEFT JOIN ' . Organisation::TABLE . ' USING(OrganisationsID)
        WHERE
            useraccount.`NutzerID` = userAssignment.`nutzerid`
        ORDER BY organisation.Organisationsname, behoerde.Name
    ';

    public function getEntityMapping()
    {
        return [
            'id' => 'useraccount.Name',
            'password' => 'useraccount.Passworthash',
            'email' => 'useraccount.email',
            'lastLogin' => 'useraccount.lastUpdate',
            'rights__superuser' => self::expression('`useraccount`.`Berechtigung` = 90'),
            'rights__organisation' => self::expression('`useraccount`.`Berechtigung` >= 70'),
            'rights__department' => self::expression('`useraccount`.`Berechtigung` >= 50'),
            'rights__cluster' => self::expression('`useraccount`.`Berechtigung` >= 40'),
            'rights__useraccount' => self::expression('`useraccount`.`Berechtigung` >= 40'),
            'rights__scope' => self::expression('`useraccount`.`Berechtigung` >= 30'),
            'rights__availability' => self::expression('`useraccount`.`Berechtigung` >= 20'),
            'rights__ticketprinter' => self::expression('`useraccount`.`Berechtigung` >= 15'),
            'rights__sms' => self::expression('`useraccount`.`Berechtigung` >= 10'),
            'rights__audit' => self::expression('`useraccount`.`Berechtigung` = 5 OR `useraccount`.`Berechtigung` = 90'),
            'rights__basic' => self::expression('`useraccount`.`Berechtigung` >= 0'),
        ];
    }

    public function addConditionLoginName($loginName)
    {
        $this->query->where('useraccount.Name', '=', $loginName);
        return $this;
    }

    public function addConditionUserId($userId)
    {
        $this->query->where('useraccount.NutzerID', '=', $userId);
        return $this;
    }

    public function addConditionPassword($password)
    {
        $this->query->where('useraccount.Passworthash', '=', $password);
        return $this;
    }

    public function addConditionXauthKey($xAuthKey)
    {
        $this->query->where('useraccount.SessionID', '=', $xAuthKey);
        return $this;
    }

    public function addConditionDepartmentAndSearch($departmentId, $queryString = null, $orWhere = false)
    {

        $this->leftJoin(
            new Alias(static::TABLE_ASSIGNMENT, 'useraccount_department'),
            'useraccount.NutzerID',
            '=',
            'useraccount_department.nutzerid'
        );

        $this->query->where('useraccount_department.behoerdenid', '=', $departmentId);

        if ($queryString) {
            $condition = function (\Solution10\SQL\ConditionBuilder $query) use ($queryString) {
                $queryString = trim($queryString);
                $query->orWith('useraccount.NutzerID', 'LIKE', "%$queryString%");
                $query->orWith('useraccount.Name', 'LIKE', "%$queryString%");
                $query->orWith('useraccount.email', 'LIKE', "%$queryString%");
            };

            if ($orWhere) {
                $this->query->orWhere($condition);
            } else {
                $this->query->where($condition);
            }
        }

        return $this;
    }

    public function addConditionRoleLevel($roleLevel)
    {
        $this->query->where('useraccount.Berechtigung', '=', $roleLevel);
        return $this;
    }

    public function addConditionDepartmentId($departmentId)
    {
        $this->leftJoin(
            new Alias(static::TABLE_ASSIGNMENT, 'useraccount_department'),
            'useraccount.NutzerID',
            '=',
            'useraccount_department.nutzerid'
        );
        $this->query->where('useraccount_department.behoerdenid', '=', $departmentId);
        return $this;
    }

    public function addConditionSearch($queryString, $orWhere = false)
    {
        $condition = function (\Solution10\SQL\ConditionBuilder $query) use ($queryString) {
            $queryString = trim($queryString);
            $query->orWith('useraccount.NutzerID', 'LIKE', "%$queryString%");
            $query->orWith('useraccount.Name', 'LIKE', "%$queryString%");
            $query->orWith('useraccount.email', 'LIKE', "%$queryString%");
        };
        if ($orWhere) {
            $this->query->orWhere($condition);
        } else {
            $this->query->where($condition);
        }
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Useraccount $entity)
    {
        $data = array();
        $data['Name'] = $entity->id;
        $data['email'] = (isset($entity->email)) ? $entity->email : null;
        $data['Passworthash'] = (isset($entity->password)) ? $entity->password : null;
        $data['Berechtigung'] = $entity->getRightsLevel();
        $data['BehoerdenID'] = 0;
        if (!$entity->isSuperUser() && isset($entity->departments) && 0 < $entity->departments->count()) {
            $data['BehoerdenID'] = $entity->departments->getFirst()->id;
        }
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
        $data[$this->getPrefixed("lastLogin")] = ('0000-00-00' != $data[$this->getPrefixed("lastLogin")]) ?
            strtotime($data[$this->getPrefixed("lastLogin")]) :
            null;
        return $data;
    }
}
