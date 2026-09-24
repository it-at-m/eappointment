<?php

namespace BO\Zmsbackend\Helper;

use BO\Slim\Render;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ExchangeAccessFilter
{
    protected static $filterList = [
        'scope.id' => 'getFilteredEntityByScope',
        'department.id' => 'getFilteredEntityByDepartment',
        'organisation.id' => 'getFilteredEntityByOrganisation',
        'useraccount.permissions' => 'getFilteredEntityByUseraccountPermissions',
        'useraccount.permissions.superuser' => 'getFilteredEntityByUseraccountSuperuser',
    ];

    protected static $exchangeEntity = null;

    protected static $filteredEntity = null;

    protected static $workstation = null;

    protected static $organisationList = null;

    /** @var array<string, true>|null */
    protected static $allowedScopeIds = null;

    /** @var array<string, true>|null */
    protected static $allowedDepartmentIds = null;

    public function __construct($exchangeEntity, $workstation)
    {
        static::$exchangeEntity = $exchangeEntity;
        static::$workstation = $workstation;
        static::$organisationList = null;
        static::$allowedScopeIds = null;
        static::$allowedDepartmentIds = null;
    }

    /**
     * @return \BO\Zmsentities\Exchange
     *
     */
    public function getFilteredEntity()
    {
        static::$filteredEntity = clone static::$exchangeEntity;
        if (static::$workstation->getUseraccount()->isSuperUser()) {
            return static::$filteredEntity;
        }

        foreach (static::$exchangeEntity->dictionary as $entry) {
            if ($entry['reference'] && isset(static::$filterList[$entry['reference']])) {
                $filterMethod = self::$filterList[$entry['reference']];
                foreach (static::$filteredEntity->data as $key => $data) {
                    static::$filterMethod($data[$entry['position']], $key);
                }
            }
        }

        return static::$filteredEntity;
    }

    /**
     * @psalm-api
     */
    protected static function getFilteredEntityByUseraccountPermissions($permission, $filteredKey): void
    {
        if (! static::$workstation->getUseraccount()->hasPermissions([$permission])) {
            unset(static::$filteredEntity->data[$filteredKey]);
        }
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     *
     * @psalm-api
     */
    protected static function getFilteredEntityByUseraccountSuperuser($unused, $filteredKey): void
    {
        if (! static::$workstation->getUseraccount()->isSuperUser()) {
            unset(static::$filteredEntity->data[$filteredKey]);
        }
    }

    /**
     * @psalm-api
     */
    protected static function getFilteredEntityByScope($entityId, $filteredKey): void
    {
        if (! static::$workstation->getUseraccount()->hasPermissions(['scope'])) {
            return;
        }

        if (! isset(static::allowedScopeIds()[(string) $entityId])) {
            unset(static::$filteredEntity->data[$filteredKey]);
        }
    }

    /**
     * @psalm-api
     */
    protected static function getFilteredEntityByDepartment($entityId, $filteredKey): void
    {
        if (! static::$workstation->getUseraccount()->hasPermissions(['department'])) {
            return;
        }

        if (! isset(static::allowedDepartmentIds()[(string) $entityId])) {
            unset(static::$filteredEntity->data[$filteredKey]);
        }
    }

    /**
     * @psalm-api
     */
    protected static function getFilteredEntityByOrganisation($entityId, $filteredKey): void
    {
        if (! static::$workstation->getUseraccount()->hasPermissions(['organisation'])) {
            return;
        }

        if (static::$organisationList === null) {
            static::$organisationList = static::getOrganisationListByDepartments();
        }

        if (! static::$organisationList->hasEntity($entityId)) {
            unset(static::$filteredEntity->data[$filteredKey]);
        }
    }

    /**
     * @return array<string, true>
     */
    protected static function allowedScopeIds(): array
    {
        if (static::$allowedScopeIds === null) {
            static::$allowedScopeIds = [];
            foreach (static::$workstation->getScopeListFromAssignedDepartments() as $scope) {
                static::$allowedScopeIds[(string) $scope->id] = true;
            }
        }

        return static::$allowedScopeIds;
    }

    /**
     * @return array<string, true>
     */
    protected static function allowedDepartmentIds(): array
    {
        if (static::$allowedDepartmentIds === null) {
            static::$allowedDepartmentIds = [];
            foreach (static::$workstation->getDepartmentList() as $department) {
                static::$allowedDepartmentIds[(string) $department->id] = true;
            }
        }

        return static::$allowedDepartmentIds;
    }

    protected static function getOrganisationListByDepartments(): \BO\Zmsentities\Collection\OrganisationList
    {
        $organisationList = new \BO\Zmsentities\Collection\OrganisationList();
        foreach (static::$workstation->getDepartmentList() as $department) {
            $organisation = (new \BO\Zmsbackend\Organisation\Service\Organisation())->readByDepartmentId($department->id);
            if ($organisation && $organisation instanceof \BO\Zmsentities\Organisation) {
                $organisationList->addEntity($organisation);
            }
        }
        return $organisationList;
    }
}
