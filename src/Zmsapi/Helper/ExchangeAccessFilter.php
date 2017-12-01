<?php

namespace BO\Zmsapi\Helper;

use \BO\Slim\Render;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ExchangeAccessFilter
{
    protected static $exchangeEntity = null;

    protected static $filteredEntity = null;

    protected static $workstation = null;

    public function __construct($exchangeEntity)
    {
        static::$exchangeEntity = $exchangeEntity;

        static::$workstation = User::readWorkstation(2);
    }

    /**
     * @return \BO\Zmsentities\Exchange
     *
     */
    public function getFilteredEntity()
    {
        static::$filteredEntity = clone static::$exchangeEntity;
        foreach (static::$exchangeEntity->dictionary as $entry) {
            $reference = static::$exchangeEntity->getReferenceByString($entry['reference']);
            if (isset($reference['entity'])) {
                $filterMethod = 'getFilteredEntityBy' . ucfirst($reference['entity']);
                if (method_exists($this, $filterMethod)) {
                    static::$filterMethod($entry['position']);
                }
            }
        }
        return static::$filteredEntity;
    }

    protected static function getFilteredEntityByScope($position = 0)
    {
        if (static::$workstation->getUseraccount()->hasRights(['scope'])) {
            foreach (static::$filteredEntity->data as $key => $entry) {
                $idList = explode(',', $entry[$position]);
                foreach ($idList as $entityId) {
                    if (! static::$workstation->getScopeList()->hasEntity($entityId)) {
                        unset(static::$filteredEntity->data[$key]);
                    }
                }
            }
        }
    }

    protected static function getFilteredEntityByDepartment($position = 0)
    {
        if (static::$workstation->getUseraccount()->hasRights(['department'])) {
            foreach (static::$filteredEntity->data as $key => $entry) {
                $idList = explode(',', $entry[$position]);
                foreach ($idList as $entityId) {
                    if (! static::$workstation->getDepartmentList()->hasEntity($entityId)) {
                        unset(static::$filteredEntity->data[$key]);
                    }
                }
            }
        }
    }

    protected static function getFilteredEntityByOrganisation($position = 0)
    {
        if (static::$workstation->getUseraccount()->hasRights(['organisation'])) {
            foreach (static::$filteredEntity->data as $key => $entry) {
                $idList = explode(',', $entry[$position]);
                foreach ($idList as $entityId) {
                    if (! static::getOrganisationListByDepartments()->hasEntity($entityId)) {
                        unset(static::$filteredEntity->data[$key]);
                    }
                }
            }
        }
    }

    protected static function getOrganisationListByDepartments()
    {
        $organisationList = new \BO\Zmsentities\Collection\OrganisationList();
        foreach (static::$workstation->getDepartmentList() as $department) {
            $organisation = (new \BO\Zmsdb\Organisation())->readByDepartmentId($department->id);
            if ($organisation && $organisation instanceof \BO\Zmsentities\Organisation) {
                $organisationList->addEntity($organisation);
            }
        }
        return $organisationList;
    }
}
