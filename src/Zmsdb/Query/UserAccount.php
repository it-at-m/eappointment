<?php

namespace BO\Zmsdb\Query;

class UserAccount extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'nutzer';

    public function getEntityMapping()
    {
        return [
            'id' => 'userAccount.Name',
            'lastLogin' => 'userAccount.Datum',
            'rights__superuser' => self::expression('`userAccount`.`Berechtigung` = 90'),
            'rights__organisation' => self::expression('`userAccount`.`Berechtigung` >= 70'),
            'rights__department' => self::expression('`userAccount`.`Berechtigung` >= 50'),
            'rights__cluster' => self::expression('`userAccount`.`Berechtigung` >= 40'),
            'rights__useraccount' => self::expression('`userAccount`.`Berechtigung` >= 30'),
            'rights__scope' => self::expression('`userAccount`.`Berechtigung` >= 20'),
            'rights__availability' => self::expression('`userAccount`.`Berechtigung` >= 15'),
            'rights__ticketprinter' => self::expression('`userAccount`.`Berechtigung` >= 10'),
            'rights__sms' => self::expression('`userAccount`.`Berechtigung` >= 0'),
            'departments__0__id' => 'userAccount.BehoerdenID',
        ];
    }

    public function addJoin()
    {
        $this->query->leftJoin(
            new Alias(Department::TABLE, 'department'),
            'userAccount.BehoerdenID',
            '=',
            'department.BehoerdenID'
        );
        $departmentQuery = new Department($this->query);
        $departmentQuery->addEntityMappingPrefixed($this->getPrefixed('departments__0__'));
        return [$departmentQuery];
    }

    public function getReferenceMapping()
    {
        return [
            'departments__0__$ref' => self::expression('CONCAT("/department/", `userAccount`.`BehoerdenID`, "/")'),
        ];
    }

    public function addConditionLoginName($loginName)
    {
        $this->query->where('userAccount.Name', '=', $loginName);
        return $this;
    }

    public function addConditionXauthKey($xAuthKey)
    {
        $this->query->where('userAccount.SessionID', '=', $xAuthKey);
        return $this;
    }


    public function reverseEntityMapping(\BO\Zmsentities\UserAccount $entity)
    {
        $data = array();
        $data['Name'] = $entity->id;
        $data['Passworthash'] = $entity->getEncryptedPassword();
        $data['Berechtigung'] = $entity->getRightsLevel();
        $data['BehoerdenID'] = $entity->getDepartmentId();
        //default values because of strict mode
        $data['notrufinitiierung'] = 0;
        $data['notrufantwort'] = 0;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
