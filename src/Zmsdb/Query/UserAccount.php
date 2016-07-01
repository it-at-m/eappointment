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
            'lastlogin' => 'userAccount.Datum',
            'id' => 'userAccount.Name',
            'rights' => 'userAccount.Berechtigung'
        ];
    }

    public function addConditionLoginName($loginName)
    {
        $this->query->where('userAccount.Name', '=', $loginName);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\UserAccount $entity)
    {
        $data = array();
        $data['id'] = $entity->loginName;
        $data['Passworthash'] = $entity->password;
        $data['Berechtigung'] = $entity->getRights();

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
