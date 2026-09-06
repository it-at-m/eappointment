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
  * @extends Base<Settings, \BO\Zmsdldb\Entity\Base>
  */
class Setting extends Base
{
    /**
     * @return Settings
     */
    #[\Override]
    protected function parseData(mixed $data)
    {
        return new Settings($data['data']['settings']);
    }

    /** @psalm-api */
    public function fetchName(mixed $name): \BO\Zmsdldb\Entity\Base|string|false|null
    {
        return $this->fetchId($name);
    }
}
