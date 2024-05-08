<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Mailtemplate as Mailtemplate;

class MailTemplates extends Base
{
    /**
     *
     * @return \BO\Zmsentities\Mailtemplate
     */
    public function readEntity()
    {
        $query = Query\Mailtemplate::QUERY_SELECT;
        $config = $this->fetchData($query);
        return $config;
    }


    public function readList()
    {
        $query = new Query\Mailtemplate(Query\Base::SELECT);
        $query->addEntityMapping();
        $logList = new \BO\Zmsentities\Collection\MailtemplateList($this->fetchList($query, new Mailtemplate()));
        return $logList;
    }


    public function updateEntity(MailTemplate $config)
    {
        $compareEntity = $this->readEntity();
        $result = false;
        $query = new Query\Mailtemplate(Query\Base::REPLACE);
        foreach ($config as $key => $item) {
            if (is_array($item)) {
                foreach ($item as $itemName => $itemValue) {
                    if ($itemValue && $compareEntity->getPreference($key, $itemName) != $itemValue) {
                        $query->addValues(array(
                            'name' => $key .'__'. $itemName,
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
        return ($result) ? $this->readEntity() : null;
    }

    public function readProperty($property, $forUpdate = false)
    {
        $sql = Query\Mailtemplate::QUERY_SELECT_PROPERTY;
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchValue($sql, [$property]);
    }

    public function replaceProperty($property, $value)
    {
        return $this->perform(Query\Mailtemplate::QUERY_REPLACE_PROPERTY, [
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
        $query = new Query\Mailtemplate(Query\Base::DELETE);
        $query->addConditionName($property);
        return $this->deleteItem($query);
    }

    protected function fetchData($querySql)
    {
        $splittedHash = array();
        $dataList = $this->getReader()->fetchAll($querySql);
        print_r($dataList);
        foreach ($dataList as $data) {
            $splittedHash[$data['name']] = $data['value'];
        }
        echo "<br/>";
        print_r($splittedHash);
        die('here');

        return new Mailtemplate($splittedHash);
    }

    protected function getSpecifiedValue($value)
    {
        if (is_bool($value)) {
            return ($value) ? 1 : 0;
        }
        return trim($value);
    }
}
