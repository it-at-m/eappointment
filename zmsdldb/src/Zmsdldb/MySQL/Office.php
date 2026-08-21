<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\MySQL;

use BO\Zmsdldb\MySQL\Entity\Office as Entity;
use BO\Zmsdldb\MySQL\Collection\Offices as Collection;
use BO\Zmsdldb\Elastic\Office as Base;

/**
  *
  */
/** @psalm-api */
class Office extends Base
{
    protected static Collection|array $officeList = [];

    #[\Override]
    protected function parseData(mixed $data): Collection
    {
        return $this->getItemList();
    }

    #[\Override]
    public function getItemList(): Collection
    {
        try {
            if (!static::$officeList instanceof Collection) {
                $officeListJson = $this->access()->fromSetting()->fetchName('office');
                $officeList = is_string($officeListJson) ? json_decode($officeListJson, true) : [];
                if (!is_array($officeList)) {
                    $officeList = [];
                }

                static::$officeList = new Collection();
                foreach ($officeList as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $office = new Entity($item);
                    if (isset($item['path'])) {
                        static::$officeList[$item['path']] = $office;
                    }
                    if (isset($item['plural'])) {
                        static::$officeList[$item['plural']] = $office;
                    }
                }
                #echo '<pre>' . htmlspecialchars(print_r((static::$officeList),1)) . '</pre>';exit;
            }
            return static::$officeList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[\Override]
    public function fetchList(): Collection
    {
        return $this->getItemList();
    }

    #[\Override]
    public function fetchId(mixed $itemId): Entity|false
    {
        $list = $this->fetchList();
        $office = $list[$itemId] ?? false;
        return $office instanceof Entity ? $office : false;
    }

    #[\Override]
    public function fetchPath(mixed $itemId): Entity|false
    {
        return $this->fetchId($itemId);
    }
}
