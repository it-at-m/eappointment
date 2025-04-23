<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\File;

use BO\Dldb\Collection\Settings;
use BO\Dldb\Entity\Setting as Entity;

/**
  * Common methods shared by access classes
  *
  */
class Setting extends Base
{
    protected function parseData($data)
    {
        return new Settings($data['data']['settings']);
    }

    public function fetchName($name)
    {
        return $this->fetchId($name);
    }
}
