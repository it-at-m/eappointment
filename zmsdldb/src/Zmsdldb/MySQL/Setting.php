<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\MySql;

use BO\Zmsdldb\File\Setting as Base;

/**
  *
  */
class Setting extends Base
{
    public function fetchName($name)
    {
        try {
            $sql = 'SELECT value FROM setting WHERE name = ?';

            $stm = $this->access()->prepare($sql);
            $stm->execute([(string)$name]);

            $settingValue = $stm->fetchColumn();

            return $settingValue ?? null;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
