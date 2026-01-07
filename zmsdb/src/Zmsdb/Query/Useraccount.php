<?php

namespace BO\Zmsdb\Query;

use BO\Slim\Application as App;

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
        SELECT behoerde.`BehoerdenID` AS id
        FROM ' . Department::TABLE . '
        ORDER BY behoerde.Name
    ';

    const QUERY_READ_ASSIGNED_DEPARTMENTS = '
        SELECT userAssignment.`behoerdenid` AS id
        FROM ' . self::TABLE_ASSIGNMENT . ' userAssignment
        LEFT JOIN ' . self::TABLE . ' useraccount ON useraccount.Name = :useraccountName
        WHERE
            useraccount.`NutzerID` = userAssignment.`nutzerid`
        ORDER BY userAssignment.`behoerdenid`
    ';

    const QUERY_READ_ASSIGNED_DEPARTMENTS_FOR_ALL = '
        SELECT useraccount.Name as useraccountName,
            userAssignment.`behoerdenid` AS id
        FROM ' . self::TABLE_ASSIGNMENT . ' userAssignment
        LEFT JOIN ' . self::TABLE . ' useraccount ON useraccount.NutzerID = userAssignment.nutzerid
        WHERE
            useraccount.Name IN (:useraccountNames)
        ORDER BY useraccount.Name, userAssignment.`behoerdenid`
    ';

    public function getEntityMapping()
    {
        return [
            'id' => 'useraccount.Name',
            'password' => 'useraccount.Passworthash',
            'lastLogin' => 'useraccount.lastUpdate',
            'rights__superuser' => self::expression('`useraccount`.`Berechtigung` = 90'),
            'rights__organisation' => self::expression('`useraccount`.`Berechtigung` >= 70'),
            'rights__department' => self::expression('`useraccount`.`Berechtigung` >= 50'),
            'rights__cluster' => self::expression('`useraccount`.`Berechtigung` >= 40'),
            'rights__useraccount' => self::expression('`useraccount`.`Berechtigung` >= 40'),
            'rights__scope' => self::expression('`useraccount`.`Berechtigung` >= 30'),
            'rights__departmentStats' => self::expression('`useraccount`.`Berechtigung` >= 25'),
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
        $this->query->where('useraccount.SessionExpiry', '>', date('Y-m-d H:i:s', time() - App::SESSION_DURATION));
        return $this;
    }

    public function addConditionRoleLevel($roleLevel)
    {
        $this->query->where('useraccount.Berechtigung', '=', $roleLevel);
        return $this;
    }

    public function addConditionSearch($queryString, $orWhere = false)
    {
        $condition = function (\BO\Zmsdb\Query\Builder\ConditionBuilder $query) use ($queryString) {
            $queryString = trim($queryString);
            $query->orWith('useraccount.NutzerID', 'LIKE', "%$queryString%");
            $query->orWith('useraccount.Name', 'LIKE', "%$queryString%");
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

    public function addConditionDepartmentIds(array $departmentIds)
    {
        $this->setDistinctSelect();
        $this->innerJoin(
            new Alias(static::TABLE_ASSIGNMENT, 'useraccount_department'),
            'useraccount.NutzerID',
            '=',
            'useraccount_department.nutzerid'
        );
        $this->query->where('useraccount_department.behoerdenid', 'IN', $departmentIds);
        return $this;
    }

    public function addConditionDepartmentIdsAndSearch(array $departmentIds, $queryString = null, $orWhere = false): self
    {
        $this->addConditionDepartmentIds($departmentIds);

        if ($queryString) {
            $this->addConditionSearch($queryString, $orWhere);
        }

        return $this;
    }

    public function addConditionExcludeSuperusers(): self
    {
        $this->query->where('useraccount.Berechtigung', '!=', 90);
        return $this;
    }

    public function addOrderByName(): self
    {
        $this->query->orderBy('useraccount.Name', 'ASC');
        return $this;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function addConditionWorkstationAccess($workstationUserId, array $workstationDepartmentIds, $isWorkstationSuperuser = false): self
    {
        // Superusers can access all useraccounts, no filtering needed
        if ($isWorkstationSuperuser) {
            return $this;
        }

        // Always exclude superusers for non-superuser workstation users
        $this->query->where('useraccount.Berechtigung', '!=', 90);

        // If no departments, only exclude superusers (already done above)
        if (empty($workstationDepartmentIds)) {
            return $this;
        }

        // Ensure we have a join to nutzerzuordnung for target useraccounts
        $this->setDistinctSelect();
        $this->innerJoin(
            new Alias(static::TABLE_ASSIGNMENT, 'useraccount_department'),
            'useraccount.NutzerID',
            '=',
            'useraccount_department.nutzerid'
        );

        // Target useraccount must share at least one department with workstation user
        $this->query->where('useraccount_department.behoerdenid', 'IN', $workstationDepartmentIds);

        return $this;
    }
}
