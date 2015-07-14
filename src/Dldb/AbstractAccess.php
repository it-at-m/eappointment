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
        $actionType = 'none';
        $instanceName = 'Missing';
        $actionName = 'Nothing';
        if (0 === strpos($functionName, 'fetch')) {
            $actionType = 'fetch';
            $instanceName = $this->getInstanceOnName($functionName, 5);
            $actionName = substr($functionName, 5 + strlen($instanceName));
            if (!$actionName) {
                $actionName = 'Id';
            }
        } elseif (0 === strpos($functionName, 'search')) {
            $actionType = 'search';
            $instanceName = $this->getInstanceOnName($functionName, 6);
            $actionName = substr($functionName, 6 + strlen($instanceName));
            if (!$actionName) {
                $actionName = 'All';
            }
        }
        $accessInstance = $this->getInstanceCompatibilities();
        if ($instanceName
            && $instanceName != 'Missing'
            && method_exists($accessInstance[$instanceName], $actionType . $actionName)) {
            $accessInstance[$instanceName]->setAccessInstance($this);
            return call_user_func_array(
                array($accessInstance[$instanceName], $actionType . $actionName),
                $functionArguments
            );
        }
        $classname = get_class($this);
        throw new Exception(
            "Unknown access function or instance: $classname::$functionName ($instanceName::$actionType$actionName)"
        );
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

    protected function from($instanceName)
    {
        if (array_key_exists($instanceName, $this->accessInstance)) {
            $instance = $this->accessInstance[$instanceName];
            if (null === $instance) {
                throw new Exception("Instance for accessing $instanceName is not initialized");
            }
            if ($instance instanceof \BO\Dldb\File\Base) {
                return $instance;
            }
            throw new Exception("Instance for accessing $instanceName failed");
        }
        throw new Exception("Instance for accessing $instanceName does not exists");
    }

    public function fromAuthority()
    {
        return $this->from('Authority');
    }

    public function fromBorough()
    {
        return $this->from('Borough');
    }

    public function fromLocation()
    {
        return $this->from('Location');
    }

    public function fromOffice()
    {
        return $this->from('Office');
    }

    public function fromService()
    {
        return $this->from('Service');
    }

    public function fromSetting()
    {
        return $this->from('Setting');
    }

    public function fromTopic()
    {
        return $this->from('Topic');
    }
}
