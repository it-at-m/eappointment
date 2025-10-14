<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Config as Entity;

class Config extends Base
{
    /**
     *
     * @return \BO\Zmsentities\Config
     */
    public function readEntity($disableCache = false)
    {
        $cacheKey = "config";
        if (!$disableCache) {
            $data = App::$cache->get($cacheKey);
            if (App::$cache && !empty($data)) {
                return $data;
            }
        }

        $query = Query\Config::QUERY_SELECT;
        $config = $this->fetchData($query);

        App::$cache->set($cacheKey, $config);

        return $config;
    }

    public function updateEntity(Entity $config)
    {
        App::$cache->delete('config');

        $compareEntity = $this->readEntity(true);
        $result = false;
        $query = new Query\Config(Query\Base::REPLACE);
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
        return ($result) ? $this->readEntity(true) : null;
    }

    public function readProperty($property, $forUpdate = false)
    {
        $sql = Query\Config::QUERY_SELECT_PROPERTY;
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchValue($sql, [$property]);
    }

    public function replaceProperty($property, $value)
    {
        return $this->perform(Query\Config::QUERY_REPLACE_PROPERTY, [
            'property' => $property,
            'value' => $value,
        ]);
    }

    /**
     * remove config data
     *
     *
     * @return Resource Status
     */
    public function deleteProperty($property)
    {
        $query = new Query\Config(Query\Base::DELETE);
        $query->addConditionName($property);
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
