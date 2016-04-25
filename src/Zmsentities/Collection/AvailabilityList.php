<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

class AvailabilityList extends Base
{
    public function getMaxWorkstationCount()
    {
        $max = 0;
        foreach ($this as $availability) {
            if ($availability['workstationCount']['intern'] >  $max) {
                $max = $availability['workstationCount']['intern'];
            }
        }
        return $max;
    }
}
