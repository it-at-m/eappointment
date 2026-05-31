<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\File;

use BO\Zmsdldb\Collection\Settings;
use BO\Zmsdldb\Entity\Setting as Entity;

/**
  * Common methods shared by access classes
  *
  */
class Setting extends Base
{
    /**
     * @return Settings
     */
    protected function parseData($data)
    {
        return new Settings($data['data']['settings']);
    }

    public function fetchName($name)
    {
        return $this->fetchId($name);
    }
}
