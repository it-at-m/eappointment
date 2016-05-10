<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Config as Entity;

class Config extends Base
{
    /**
     *
     * @return \BO\Zmsentities\Config
     */
    public function readEntity()
    {
        $querySql = Query\Config::QUERY_SELECT;
        $config = $this->fetchData($querySql);
        return $config;
    }

    public function updateEntity(Entity $config)
    {
        $result = false;
        $query = Query\Config::QUERY_UPDATE;
        $statement = $this->getWriter()->prepare($query);
        foreach ($config as $key => $item) {
            if (is_array($item)) {
                foreach ($item as $itemName => $itemValue) {
                    $result = $statement->execute(
                        array(
                            $key .'__'. $itemName,
                            $this->getSpecifiedValue($itemValue),
                            time()
                        )
                    );
                }
            } else {
                $result = $statement->execute(
                    array(
                        $key,
                        $this->getSpecifiedValue($item),
                        time()
                    )
                );
            }
        }
        return ($result) ? $this->readEntity() : null;
    }

    protected function fetchData($querySql)
    {
        $splittedHash = array();
        try {
            $dataList = $this->getReader()->fetchAll($querySql);
        } catch (\PDOException $pdoException) {
            $message = "SQL:". $querySql;
            throw new \Exception($message, 0, $pdoException);
        }
        foreach ($dataList as $data) {
            if (is_array($data['name'])) {
                $hash = (new Entity())->getUnflattenedArray($data['name']);
                foreach ($hash as $key => $value) {
                    $splittedHash[$key] = $value;
                }
            } else {
                $splittedHash[$data['name']] = $data['value'];
            }
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
