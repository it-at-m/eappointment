<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\MailTemplate as MailTemplate;

class MailTemplates extends Base
{
    /**
     *
     * @return \BO\Zmsentities\MailTemplate
     */
    public function readEntity()
    {
        $query = Query\MailTemplates::QUERY_SELECT;
        $config = $this->fetchData($query);
        return $config;
    }

    public function updateEntity(MailTemplate $config)
    {
        $compareEntity = $this->readEntity();
        $result = false;
        $query = new Query\MailTemplates(Query\Base::REPLACE);
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
        $sql = Query\MailTemplates::QUERY_SELECT_PROPERTY;
        if ($forUpdate) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchValue($sql, [$property]);
    }

    public function replaceProperty($property, $value)
    {
        return $this->perform(Query\MailTemplates::QUERY_REPLACE_PROPERTY, [
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
        $query = new Query\MailTemplates(Query\Base::DELETE);
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
        return new MailTemplate($splittedHash);
    }

    protected function getSpecifiedValue($value)
    {
        if (is_bool($value)) {
            return ($value) ? 1 : 0;
        }
        return trim($value);
    }
}
