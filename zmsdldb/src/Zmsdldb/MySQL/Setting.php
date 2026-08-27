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
/** @psalm-api */
class Setting extends Base
{
    #[\Override]
    public function fetchName($name): ?string
    {
        try {
            $sql = 'SELECT value FROM setting WHERE name = ?';

            $stm = $this->access()->prepare($sql);
            $stm->execute([$name]);

            $settingValue = $stm->fetchColumn();

            return is_string($settingValue) ? $settingValue : null;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
