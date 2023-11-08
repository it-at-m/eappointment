<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Dldb;

/**
 * Common methods shared by access classes
 */
class AbstractAccess
{

    protected static $showDeprecated = false;

    protected $accessInstance = array(
        'de' => array(
            'Authority' => null,
            'Borough' => null,
            'Link' => null,
            'Location' => null,
            'Office' => null,
            'Service' => null,
            'Setting' => null,
            'Topic' => null
        ),
        'en' => array(
            'Authority' => null,
            'Borough' => null,
            'Link' => null,
            'Location' => null,
            'Office' => null,
            'Service' => null,
            'Setting' => null,
            'Topic' => null
        )
    );

    protected static $accessInstanceTypes = [
        'Authority' => null,
        'Borough' => null,
        'Link' => null,
        'Location' => null,
        'Office' => null,
        'Service' => null,
        'Setting' => null,
        'Topic' => null
    ];

    public function addAccessInstanceLocale($locale = 'de')
    {
        if (!isset($this->accessInstance[$locale])) {
            $this->accessInstance[$locale] = static::$accessInstanceTypes;
        }
    }


    private function getInstanceCompatibilities()
    {
        $accessInstance = $this->accessInstance['de'];
        $accessInstance['Authorities'] = $accessInstance['Authority'];
        $accessInstance['Boroughs'] = $accessInstance['Borough'];
        $accessInstance['Offices'] = $accessInstance['Office'];
        $accessInstance['Settings'] = $accessInstance['Setting'];
        $accessInstance['Topics'] = $accessInstance['Topic'];
        $accessInstance['Locations'] = $accessInstance['Location'];
        $accessInstance['Services'] = $accessInstance['Service'];
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
            if (! $actionName) {
                $actionName = 'Id';
            }
        } elseif (0 === strpos($functionName, 'search')) {
            $actionType = 'search';
            $instanceName = $this->getInstanceOnName($functionName, 6);
            $actionName = substr($functionName, 6 + strlen($instanceName));
            if (! $actionName) {
                $actionType = "read";
                $actionName = 'SearchResultList';
            }
        }
        $accessInstance = $this->getInstanceCompatibilities();
        if ($instanceName
            && $instanceName != 'Missing'
            && method_exists($accessInstance[$instanceName], $actionType . $actionName)
        ) {
            $accessInstance[$instanceName]->setAccessInstance($this);
            return call_user_func_array(array(
                $accessInstance[$instanceName],
                $actionType . $actionName
            ), $functionArguments);
        }
        $classname = get_class($this);
        throw new Exception(
            "Unknown access function or instance: $classname::$functionName ($instanceName::$actionType$actionName)"
        );
    }

    /**
     *
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

    protected function from($instanceName, $locale = 'de')
    {
        if (array_key_exists($instanceName, $this->accessInstance[$locale])) {
            $instance = $this->accessInstance[$locale][$instanceName];
            if ($instance instanceof \BO\Dldb\File\Base) {
                return $instance;
            }
            if (null === $instance) {
                throw new Exception("Instance for accessing $instanceName ($locale) is not initialized");
            }
            throw new Exception("Instance for accessing $instanceName failed");
        }
        echo '<pre>' . print_r($this->accessInstance, 1) . '</pre>';
        //exit;
        throw new Exception("Locale for accessing $instanceName does not exists");
    }

    public function fromAuthority($locale = 'de')
    {
        return $this->from('Authority', $locale);
    }

    public function fromBorough()
    {
        return $this->from('Borough');
    }

    public function fromLink($locale = 'de')
    {
        return $this->from('Link', $locale);
    }

    public function fromLocation($locale = 'de')
    {
        return $this->from('Location', $locale);
    }

    public function fromOffice()
    {
        return $this->from('Office');
    }

    public function fromService($locale = 'de')
    {
        return $this->from('Service', $locale);
    }

    public function fromSetting()
    {
        return $this->from('Setting');
    }

    public function fromTopic($locale = 'de')
    {
        return $this->from('Topic', $locale);
    }
}
