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
    public const string PRIMARY = 'id';

    public static $schema = "useraccount.json";

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'permissions' => [
                "appointment" => false,
                "availability" => false,
                "calldisplay" => false,
                "capacityreport" => false,
                "cherrypick" => false,
                "cluster" => false,
                "config" => false,
                "counter" => false,
                "customersearch" => false,
                "dayoff" => false,
                "department" => false,
                "emergency" => false,
                "finishedqueue" => false,
                "finishedqueuepast" => false,
                "jurisdiction" => false,
                "logs" => false,
                "mailtemplates" => false,
                "missedqueue" => false,
                "openqueue" => false,
                "organisation" => false,
                "overviewcalendar" => false,
                "parkedqueue" => false,
                "restrictedscope" => false,
                "scope" => false,
                "source" => false,
                "statistic" => false,
                "ticketprinter" => false,
                "useraccount" => false,
                "waitingqueue" => false,
                "superuser" => false
            ],
        ];
    }

    #[\Override]
    public function addData(array|object $mergeData): static
    {
        $hasDepartments = false;
        if (is_array($mergeData) || $mergeData instanceof \ArrayAccess) {
            $hasDepartments = isset($mergeData['departments']);
        } elseif (is_object($mergeData)) {
            $hasDepartments = isset($mergeData->departments);
        }
        if ($hasDepartments && !($this['departments'] ?? null) instanceof Collection\DepartmentList) {
            $this->departments = new Collection\DepartmentList();
        }
        return parent::addData($mergeData);
    }

    /**
     * @return true
     */
    public function hasProperties(): bool
    {
        foreach (func_get_args() as $property) {
            if (!$this->toProperty()->$property->get()) {
                throw new Exception\UserAccountMissingProperties("Missing property " . htmlspecialchars($property));
                return false;
            }
        }
        return true;
    }

    public function getDepartmentList(): Collection\DepartmentList
    {
        if (!isset($this['departments'])) {
            return new Collection\DepartmentList();
        }
        if (!$this->departments instanceof Collection\DepartmentList) {
            $this->departments = new Collection\DepartmentList($this->departments);
            foreach ($this->departments as $key => $department) {
                $this->departments[$key] = new Department($department);
            }
        }
        return $this->departments;
    }

    public function addDepartment(Department|array $department): static
    {
        if (!isset($this['departments'])) {
            $this->departments = new Collection\DepartmentList();
        } elseif (!$this->departments instanceof Collection\DepartmentList) {
            $this->getDepartmentList();
        }
        $this->departments[] = $department;
        return $this;
    }

    public function getDepartment($departmentId)
    {
        foreach ($this->getDepartmentList() as $department) {
            if ($department['id'] == $departmentId) {
                return $department;
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


    public function setPermissions(): static
    {
        $givenPermissions = func_get_args();
        foreach ($givenPermissions as $permission) {
            if (Property::__keyExists($permission, $this->permissions)) {
                $this->permissions[$permission] = true;
            }
        }
        return $this;
    }

    /**
     * Returns true when the user has all of the given permissions.
     */
    public function hasPermissions(array $requiredPermissions): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $permissions = $this->toProperty()->permissions ?? null;

        foreach ($requiredPermissions as $required) {
            if ($required instanceof Useraccount\RightsInterface) {
                if (! $required->validateUseraccount($this)) {
                    return false;
                }
                continue;
            }

            if (! ($permissions?->$required?->get() ?? false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true when the user has any of the given permissions.
     */
    public function hasAnyPermission(array $requiredPermissions): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        $permissions = $this->toProperty()->permissions ?? null;

        foreach ($requiredPermissions as $required) {
            if ($required instanceof Useraccount\RightsInterface) {
                if ($required->validateUseraccount($this)) {
                    return true;
                }
                continue;
            }

            if ($permissions?->$required?->get() ?? false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true when the user has only the given permission and no other permission.
     */
    public function hasExclusivePermission(string $permission): bool
    {
        if ($this->isSuperUser()) {
            return false;
        }

        $permissions = $this['permissions'] ?? [];
        $requiredPermission = $permissions[$permission] ?? false;
        if (!is_array($permissions) || !$requiredPermission || '0' === $requiredPermission) {
            return false;
        }

        foreach ($permissions as $name => $enabled) {
            if ($permission === $name || 'superuser' === $name) {
                continue;
            }
            if ($enabled && '0' !== $enabled) {
                return false;
            }
        }

        return true;
    }
    public function getRoles(): array
    {
        $roles = $this->roles ?? [];
        if (is_array($roles)) {
            return $roles;
        }
        if (is_string($roles)) {
            return array_values(array_filter(array_map('trim', explode(',', $roles)), function ($role) {
                return $role !== '';
            }));
        }
        return [];
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function testPermissions(array $requiredPermissions): static
    {
        if (! $this->hasId()) {
            throw new Exception\UserAccountMissingLogin();
        }

        if (! $this->hasPermissions($requiredPermissions)) {
            throw new Exception\UserAccountMissingRights(
                "Missing permissions " . htmlspecialchars(implode(',', $requiredPermissions))
            );
        }

        return $this;
    }

    public function testAnyPermission(array $requiredPermissions): static
    {
        if (! $this->hasId()) {
            throw new Exception\UserAccountMissingLogin();
        }

        if (! $this->hasAnyPermission($requiredPermissions)) {
            throw new Exception\UserAccountMissingRights(
                "Missing any of permissions " . htmlspecialchars(implode(',', $requiredPermissions))
            );
        }

        return $this;
    }

    public function isOveraged(\DateTimeInterface $dateTime): bool
    {
        if (Property::__keyExists('lastLogin', $this)) {
            $lastLogin = (new \DateTimeImmutable())->setTimestamp($this['lastLogin'])->modify('23:59:59');
            return ($lastLogin < $dateTime);
        }
        return false;
    }

    public function isSuperUser(): bool
    {
        return $this->toProperty()->permissions?->superuser?->get() ?? false;
    }

    public function getDepartmentById($departmentId): Department
    {
        foreach ($this->getDepartmentList() as $department) {
            if ($departmentId == $department['id']) {
                return new Department($department);
            }
        }
        return new Department();
    }

    public function getDepartmentByIds(array $departmentIds): Department
    {
        foreach ($this->getDepartmentList() as $department) {
            if (in_array($department['id'], $departmentIds)) {
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

    public function setPassword($input): static
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

    public function withDepartmentList(): static
    {
        $departmentList = new Collection\DepartmentList();
        $entity = clone $this;
        foreach ($this['departments'] ?? [] as $department) {
            if ($department instanceof Department) {
                $departmentList->addEntity($department);
            } else {
                $departmentList->addEntity(new Department(
                    is_array($department) ? $department : ['id' => $department]
                ));
            }
        }
        $entity->departments = $departmentList;
        return $entity;
    }

    /**
     * @return static
     */
    #[\Override]
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
            $result = $this->password === md5($password);
        } else {
            $result = password_verify($password, $this->password);
        }

        // on passed validation check if the hash needs updating.
        if ($result && $this->isPasswordNeedingRehash()) {
            $this->password = $this->getHash($password);
        }

        return $this;
    }

    public function withVerifiedHash($password): static
    {
        $useraccount = clone $this;
        if ($useraccount->isPasswordNeedingRehash()) {
            $useraccount->setVerifiedHash($password);
        }
        return $useraccount;
    }

    public function isPasswordNeedingRehash(): bool
    {
        return password_needs_rehash($this->password, PASSWORD_DEFAULT);
    }

    /**
     * set salted hash by string
     *
     * @return string $hash
    */
    public function getHash(string $string)
    {
        $hash = password_hash($string, PASSWORD_DEFAULT);
        return $hash;
    }

    /**
     * @return static
     */
    #[\Override]
    public function withLessData()
    {
        unset($this->departments);

        return $this;
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
        $department = new Department(['id' => 0]);
        $entity->addDepartment($department);
        $password = substr(str_shuffle($entity->id . uniqid()), 0, 8);
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
