<?php

namespace BO\Zmsdb;

class Preferences extends Base
{
    const REPLACE_SKIPPED = 'skipped';

    /**
     * @psalm-param 'scope' $entityName
     * @psalm-param 'appointment' $groupName
     * @psalm-param 'endInDaysDefault'|'startInDaysDefault' $name
     */
    public function readProperty(string $entityName, $entityId, string $groupName, string $name, bool $forUpdate = false)
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

    public function readChangeDateTime(string $entityName, $entityId, string $groupName, string $name, $forUpdate = false): \DateTimeImmutable
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
            $timeString .= ' ' . \BO\Zmsdb\Connection\Select::$connectionTimezone;
        }
        return new \DateTimeImmutable($timeString);
    }

    /**
     * @psalm-param 'scope' $entityName
     */
    public function replaceProperty(string $entityName, $entityId, $groupName, $name, $value)
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
     *
     * @param key-of<TArray> $name
     *
     * @psalm-param 'scope' $entityName
     */
    public function deleteProperty(string $entityName, $entityId, $groupName, $name)
    {
        return $this->perform(Query\Preferences::QUERY_DELETE_PROPERTY, [
            "entityName" => $entityName,
            "entityId" => $entityId,
            "groupName" => $groupName,
            "name" => $name,
        ]);
    }
}
