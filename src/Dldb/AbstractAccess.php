<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
  * Common methods shared by access classes
  *
  */
class AbstractAccess
{
    /**
     * @return Array
     */
    public function fetchServiceCombinations($service_csv)
    {
        return $this->fetchServiceList($this->fetchServiceLocationCsv($service_csv));
    }

    /**
     * @return String
     */
    protected function fetchServiceLocationCsv($service_csv)
    {
        $locationlist = $this->fetchLocationList($service_csv);
        $locationIdList = array();
        foreach ($locationlist as $location) {
            $locationIdList[] = $location['id'];
        }
        return implode(',', $locationIdList);
    }
}
