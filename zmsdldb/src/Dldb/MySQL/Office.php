<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\MySQL;

use \BO\Dldb\MySQL\Entity\Office as Entity;
use \BO\Dldb\MySQL\Collection\Offices as Collection;
use \BO\Dldb\Elastic\Office as Base;

/**
  *
  */
class Office extends Base
{
    protected static $officeList = [];

    protected function parseData($data)
    {
        return $this->getItemList();
    }

    public function getItemList()
    {
        try {
            if (empty(static::$officeList)) {
                $officeListJson = $this->access()->fromSetting()->fetchName('office');
                $officeList = json_decode($officeListJson, true);

                static::$officeList = new Collection();
                foreach ($officeList as $item) {
                    static::$officeList[$item['path']] = new Entity($item);
                    static::$officeList[$item['plural']] = static::$officeList[$item['path']];
                }
                #echo '<pre>' . htmlspecialchars(print_r((static::$officeList),1)) . '</pre>';exit;
            }
            return static::$officeList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function fetchList()
    {
        return $this->getItemList();
    }

    public function fetchId($itemId)
    {
        $list = $this->fetchList();
        return $list[$itemId] ?? false;
    }

    public function fetchPath($itemId)
    {
        return $this->fetchId($itemId);
    }
}
