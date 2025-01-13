<?php

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(PublicMethod)
 *
 */
class Useraccount extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "useraccount.json";

    public function getDefaults()
    {
        return [
            'rights' => [
                "availability" => false,
                "basic" => true,
                "audit" => false,
                "cluster" => false,
                "department" => false,
                "organisation" => false,
                "scope" => false,
                "sms" => false,
                "superuser" => false,
                "ticketprinter" => false,
                "useraccount" => false,
            ],
            'departments' => new Collection\DepartmentList(),
        ];
    }

    public function hasProperties()
    {
        foreach (func_get_args() as $property) {
            if (!$this->toProperty()->$property->get()) {
                throw new Exception\UserAccountMissingProperties("Missing property " . htmlspecialchars($property));
                return false;
            }
        }
        return true;
    }

    public function getDepartmentList()
    {
        if (!$this->departments instanceof Collection\DepartmentList) {
            $this->departments = new Collection\DepartmentList($this->departments);
            foreach ($this->departments as $key => $department) {
                $this->departments[$key] = new Department($department);
            }
        }
        return $this->departments;
    }

    public function addDepartment($department)
    {
        $this->departments[] = $department;
        return $this;
    }

    public function getDepartment($departmentId)
    {
        if (count($this->departments)) {
            foreach ($this->getDepartmentList() as $department) {
                if ($department['id'] == $departmentId) {
                    return $department;
                }
            }
        }
        return new Department(['name' => 'Not existing']);
    }

    public function hasDepartment($departmentId)
    {
        return $this->getDepartment($departmentId)->hasId();
    }

    public function hasScope($scopeId)
    {
        return $this->getDepartmentList()->getUniqueScopeList()->hasEntity($scopeId);
    }

    /**
     * @todo Remove this function, keep no contraint on old DB schema in zmsentities
     */
    public function getRightsLevel()
    {
        return Helper\RightsLevelManager::getLevel($this->rights);
    }

    public function setRights()
    {
        $givenRights = func_get_args();
        foreach ($givenRights as $right) {
            if (Property::__keyExists($right, $this->rights)) {
                $this->rights[$right] = true;
            }
        }
        return $this;
    }

    public function hasRights(array $requiredRights)
    {
        if ($this->isSuperUser()) {
            return true;
        }
        foreach ($requiredRights as $required) {
            if ($required instanceof Useraccount\RightsInterface) {
                if (!$required->validateUseraccount($this)) {
                    return false;
                }
            } elseif (! $this->toProperty()->rights->$required->get()) {
                return false;
            }
        }
        return true;
    }

    public function testRights(array $requiredRights)
    {
        if ($this->hasId()) {
            if (!$this->hasRights($requiredRights)) {
                throw new Exception\UserAccountMissingRights(
                    "Missing rights " . htmlspecialchars(implode(',', $requiredRights))
                );
            }
        } else {
            throw new Exception\UserAccountMissingLogin();
        }
        return $this;
    }

    public function isOveraged(\DateTimeInterface $dateTime)
    {
        if (Property::__keyExists('lastLogin', $this)) {
            $lastLogin = (new \DateTimeImmutable())->setTimestamp($this['lastLogin'])->modify('23:59:59');
            return ($lastLogin < $dateTime);
        }
        return false;
    }

    public function isSuperUser()
    {
        return $this->toProperty()->rights->superuser->get();
    }

    public function getDepartmentById($departmentId)
    {
        foreach ($this->departments as $department) {
            if ($departmentId == $department['id']) {
                return new Department($department);
            }
        }
        return new Department();
    }

    public function testDepartmentById($departmentId)
    {
        $department = $this->getDepartmentById($departmentId);
        if (!$department->hasId()) {
            throw new Exception\UserAccountMissingDepartment(
                "Missing department " . htmlspecialchars($departmentId)
            );
        }
        return $department;
    }

    public function setPassword($input)
    {
        if (isset($input['password']) && '' != $input['password']) {
            $this->password = $input['password'];
        }
        if (isset($input['changePassword']) && 0 < count(array_filter($input['changePassword']))) {
            if (! isset($input['password'])) {
                $this->password = $input['changePassword'][0];
            }
            $this->changePassword = $input['changePassword'];
        }
        return $this;
    }

    public function withDepartmentList()
    {
        $departmentList = new Collection\DepartmentList();
        $entity = clone $this;
        foreach ($this->departments as $department) {
            if (! is_array($department) && ! $department instanceof Department) {
                $department = new Department(array('id' => $department));
            }
            $departmentList->addEntity($department);
        }
        $entity->departments = $departmentList;
        return $entity;
    }

    public function withCleanedUpFormData($keepPassword = false)
    {
        unset($this['save']);
        if (isset($this['password']) && '' == $this['password'] && false === $keepPassword) {
            unset($this['password']);
        }
        if (
            isset($this['changePassword']) &&
            0 == count(array_filter($this['changePassword'])) &&
            false === $keepPassword
        ) {
            unset($this['changePassword']);
        }
        if (isset($this['oidcProvider'])) {
            unset($this['oidcProvider']);
        }

        return $this;
    }

    /**
     * verify hashed password and create new if needs rehash
     *
     * @return array $useraccount
    */
    public function setVerifiedHash($password)
    {
        // Do you have old, turbo-legacy, non-crypt hashes?
        if (strpos($this->password, '$') !== 0) {
            //error_log(__METHOD__ . "::legacy_hash\n");
            $result = $this->password === md5($password);
        } else {
            //error_log(__METHOD__ . "::password_verify\n");
            $result = password_verify($password, $this->password);
        }

        // on passed validation check if the hash needs updating.
        if ($result && $this->isPasswordNeedingRehash()) {
            $this->password = $this->getHash($password);
            //error_log(__METHOD__ . "::rehash\n");
        }

        return $this;
    }

    public function withVerifiedHash($password)
    {
        $useraccount = clone $this;
        if ($useraccount->isPasswordNeedingRehash()) {
            $useraccount->setVerifiedHash($password);
        }
        return $useraccount;
    }

    public function isPasswordNeedingRehash()
    {
        return password_needs_rehash($this->password, PASSWORD_DEFAULT);
    }

    /**
     * set salted hash by string
     *
     * @return string $hash
    */
    public function getHash($string)
    {
        $hash = password_hash($string, PASSWORD_DEFAULT);
        return $hash;
    }

    /**
     * create useraccount from open id input data with random password
     *
     * @return string $entity
    */
    public function createFromOpenidData($data)
    {
        $entity = new self();
        $entity->id = $data['username'];
        $entity->email = $data['email'];
        $department = new Department(['id' => 0]);
        $entity->addDepartment($department);
        $password = substr(str_shuffle($entity->id . $entity->email), 0, 8);
        $entity->password = $this->getHash($password);
        return $entity;
    }

    /**
     * get oidc provider from $entity id if it exists
     *
     * @return string $entity
    */
    public function getOidcProviderFromName()
    {
        $providerName = '';
        if (($pos = strpos($this->id, "@")) !== false) {
            $providerName = substr($this->id, $pos + 1);
        }
        return ('' !== $providerName) ? $providerName : null;
    }
}
