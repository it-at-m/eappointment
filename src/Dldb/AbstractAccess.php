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

    protected static $showDeprecated = false;

    protected $accessInstance = array(
        'Authority' => null,
        'Borough' => null,
        'Location' => null,
        'Office' => null,
        'Service' => null,
        'Setting' => null,
        'Topic' => null,
    );

    private function getInstanceCompatibilities()
    {
        $accessInstance = $this->accessInstance;
        $accessInstance['Authorities'] = $accessInstance['Authority'];
        $accessInstance['Boroughs'] = $accessInstance['Borough'];
        $accessInstance['Locations'] = $accessInstance['Location'];
        $accessInstance['Offices'] = $accessInstance['Office'];
        $accessInstance['Services'] = $accessInstance['Service'];
        $accessInstance['Settings'] = $accessInstance['Setting'];
        $accessInstance['Topics'] = $accessInstance['Topic'];
        return $accessInstance;
    }

    /**
     * find matching function in instance
     *
     * @return Mixed
     */
    public function __call($functionName, $functionArguments)
    {
        if (self::$showDeprecated) {
            trigger_error("Deprecated access function: $functionName");
        }
        if (0 === strpos($functionName, 'fetch')) {
            $actionType = 'fetch';
            $instanceName = $this->getInstanceOnName($functionName, 5);
            $actionName = substr($functionName, 5 + strlen($instanceName));
            if (!$actionName) {
                $actionName = 'Id';
            }
        }
        $accessInstance = $this->getInstanceCompatibilities();
        if ($instanceName
            && method_exists($accessInstance[$instanceName], $actionType . $actionName)) {
            $accessInstance[$instanceName]->setAccessInstance($this);
            return call_user_func_array(
                array($accessInstance[$instanceName], $actionType . $actionName),
                $functionArguments
            );
        }
        throw new Exception("Unknown access function or instance");
    }

    /**
     * @return String InstanceName
     */
    protected function getInstanceOnName($name, $position = 0)
    {
        foreach (array_keys($this->getInstanceCompatibilities()) as $instanceName) {
            if ($position === strpos($name, $instanceName)) {
                return $instanceName;
            }
        }
        return null;
    }

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
