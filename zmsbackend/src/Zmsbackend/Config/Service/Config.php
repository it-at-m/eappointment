<?php

namespace BO\Zmsbackend\Config\Service;

use BO\Zmsbackend\Application as App;
use BO\Zmsentities\Config as Entity;

class Config extends \BO\Zmsbackend\Base
{
    /**
     *
     * @return \BO\Zmsentities\Config
     */
    public function readEntity($disableCache = false)
    {
        $cacheKey = "config";

        if (!$disableCache && App::$cache) {
            $cached = App::$cache->get($cacheKey);
            if ($cached instanceof Entity) {
                return $cached;
            }
        }

        $query = \BO\Zmsbackend\Config\Repository\Config::QUERY_SELECT;
        $config = $this->fetchData($query);

        if (App::$cache) {
            App::$cache->set($cacheKey, $config);
        }

        return $config;
    }

    public function updateEntity(Entity $config)
    {
        $compareEntity = $this->readEntity(true);
        $result = false;
        $query = new \BO\Zmsbackend\Config\Repository\Config(\BO\Zmsbackend\Query\Base::REPLACE);
        foreach ($config as $key => $item) {
            if (is_array($item)) {
                foreach ($item as $itemName => $itemValue) {
                    if ($itemValue && $compareEntity->getPreference($key, $itemName) != $itemValue) {
                        $query->addValues(array(
                            'name' => $key . '__' . $itemName,
                            'value' => $this->getSpecifiedValue($itemValue),
                            'changeTimestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                        ));
                        $result = $this->writeItem($query);
                    }
                }
            } else {
                $query->addValues(array(
                    'name' => $key,
                    'value' => $this->getSpecifiedValue($item),
                    'changeTimestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                ));
                $result = $this->writeItem($query);
            }
        }

        if (App::$cache && App::$cache->has('config')) {
            App::$cache->delete('config');
        }

        return ($result) ? $this->readEntity(true) : null;
    }

    public function readProperty($property, $forUpdate = false)
    {
        $sql = \BO\Zmsbackend\Config\Repository\Config::QUERY_SELECT_PROPERTY;
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchValue($sql, [$property]);
    }

    public function replaceProperty($property, $value)
    {
        if (App::$cache && App::$cache->has('config')) {
            App::$cache->delete('config');
        }

        return $this->perform(\BO\Zmsbackend\Config\Repository\Config::QUERY_REPLACE_PROPERTY, [
            'property' => $property,
            'value' => $value,
        ]);
    }

    /**
     * remove config data
     *
     *
     * @return Entity|null
     */
    public function deleteProperty($property)
    {
        $query = new \BO\Zmsbackend\Config\Repository\Config(\BO\Zmsbackend\Query\Base::DELETE);
        $query->addConditionName($property);

        if (App::$cache && App::$cache->has('config')) {
            App::$cache->delete('config');
        }

        return $this->deleteItem($query);
    }

    protected function fetchData($querySql)
    {
        $splittedHash = array();
        $dataList = $this->getReader()->fetchAll($querySql);
        foreach ($dataList as $data) {
            $splittedHash[$data['name']] = $data['value'];
        }
        return new Entity($splittedHash);
    }

    protected function getSpecifiedValue($value)
    {
        if (is_bool($value)) {
            return ($value) ? 1 : 0;
        }
        return trim($value);
    }
}
