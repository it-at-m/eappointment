<?php
namespace BO\Zmsdb;

class Preferences extends Base
{
    const REPLACE_SKIPPED = 'skipped';

    public function readProperty($entityName, $entityId, $groupName, $name, $forUpdate = false)
    {
        $sql = Query\Preferences::QUERY_SELECT_PROPERTY;
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchValue($sql, [
            "entityName" => $entityName,
            "entityId" => $entityId,
            "groupName" => $groupName,
            "name" => $name,
        ]);
    }

    public function readChangeDateTime($entityName, $entityId, $groupName, $name, $forUpdate = false)
    {
        $sql = Query\Preferences::QUERY_SELECT_TIMESTAMP;
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        $timeString = $this->fetchValue($sql, [
            "entityName" => $entityName,
            "entityId" => $entityId,
            "groupName" => $groupName,
            "name" => $name,
        ]);
        if (!$timeString) {
            $timeString = 'now';
        } else {
            $timeString .= ' '.\BO\Zmsdb\Connection\Select::$connectionTimezone;
        }
        return new \DateTimeImmutable($timeString);
    }

    public function replaceProperty($entityName, $entityId, $groupName, $name, $value)
    {
        $this->getWriter();
        $currentValue = $this->readProperty($entityName, $entityId, $groupName, $name, true);
        if ($currentValue != $value) {
            return $this->perform(Query\Preferences::QUERY_REPLACE_PROPERTY, [
                "entityName" => $entityName,
                "entityId" => $entityId,
                "groupName" => $groupName,
                "name" => $name,
                "value" => $value,
            ]);
        }
        return static::REPLACE_SKIPPED;
    }

    /**
     * remove Preferences data
     *
     * @return Resource Status
     */
    public function deleteProperty($entityName, $entityId, $groupName, $name)
    {
        return $this->perform(Query\Preferences::QUERY_DELETE_PROPERTY, [
            "entityName" => $entityName,
            "entityId" => $entityId,
            "groupName" => $groupName,
            "name" => $name,
        ]);
    }
}
