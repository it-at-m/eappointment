<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb;

/**
 * Common methods shared by access classes
 */
class AbstractAccess
{
    protected static bool $showDeprecated = false;

    protected mixed $accessInstance = array(
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

    protected static mixed $accessInstanceTypes = [
        'Authority' => null,
        'Borough' => null,
        'Link' => null,
        'Location' => null,
        'Office' => null,
        'Service' => null,
        'Setting' => null,
        'Topic' => null
    ];

    /**
     * @psalm-api
     */
    public function addAccessInstanceLocale(string $locale = 'de'): void
    {
        if (!isset($this->accessInstance[$locale])) {
            $this->accessInstance[$locale] = static::$accessInstanceTypes;
        }
    }


    private function getInstanceCompatibilities(): mixed
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
    public function __call(string $functionName, array $functionArguments)
    {
        if (self::$showDeprecated) {
            trigger_error("Deprecated access function: $functionName");
        }
        $actionType = 'none';
        $instanceName = 'Missing';
        $actionName = 'Nothing';
        if (0 === strpos($functionName, 'fetch')) {
            $actionType = 'fetch';
            /** @var string|null $instanceName */
            $instanceName = $this->getInstanceOnName($functionName, 5);
            $actionName = substr($functionName, 5 + strlen($instanceName ?? ''));
            if (! $actionName) {
                $actionName = 'Id';
            }
        } elseif (0 === strpos($functionName, 'search')) {
            $actionType = 'search';
            /** @var string|null $instanceName */
            $instanceName = $this->getInstanceOnName($functionName, 6);
            $actionName = substr($functionName, 6 + strlen($instanceName ?? ''));
            if (! $actionName) {
                $actionType = "read";
                $actionName = 'SearchResultList';
            }
        }
        $accessInstance = $this->getInstanceCompatibilities();
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (
            $instanceName
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
     * @return string|null InstanceName
     */
    protected function getInstanceOnName(mixed $name, int $position = 0)
    {
        foreach (array_keys($this->getInstanceCompatibilities()) as $instanceName) {
            if ($position === strpos($name, $instanceName)) {
                return $instanceName;
            }
        }
        return null;
    }

    protected function from(string $instanceName, string $locale = 'de'): File\Base
    {
        if (array_key_exists($instanceName, $this->accessInstance[$locale])) {
            $instance = $this->accessInstance[$locale][$instanceName];
            if ($instance instanceof \BO\Zmsdldb\File\Base) {
                return $instance;
            }
            if (null === $instance) {
                throw new Exception("Instance for accessing $instanceName ($locale) is not initialized");
            }
            throw new Exception("Instance for accessing $instanceName failed");
        }
        if (class_exists('\App', false) && isset(\App::$log)) {
            \App::$log->error('DLDB access locale missing', [
                'instance' => $instanceName,
                'locale' => $locale,
            ]);
        }
        throw new Exception("Locale for accessing $instanceName does not exists");
    }

    public function fromAuthority(string $locale = 'de'): File\Authority
    {
        /** @var File\Authority $instance */
        $instance = $this->from('Authority', $locale);
        return $instance;
    }

    public function fromBorough(): File\Borough
    {
        /** @var File\Borough $instance */
        $instance = $this->from('Borough');
        return $instance;
    }

    /** @psalm-api */
    public function fromLink(string $locale = 'de'): File\Link
    {
        /** @var File\Link $instance */
        $instance = $this->from('Link', $locale);
        return $instance;
    }

    public function fromLocation(string $locale = 'de'): File\Location
    {
        /** @var File\Location $instance */
        $instance = $this->from('Location', $locale);
        return $instance;
    }

    public function fromOffice(): File\Office
    {
        /** @var File\Office $instance */
        $instance = $this->from('Office');
        return $instance;
    }

    public function fromService(string $locale = 'de'): File\Service
    {
        /** @var File\Service $instance */
        $instance = $this->from('Service', $locale);
        return $instance;
    }

    public function fromSetting(string $locale = 'de'): File\Setting
    {
        /** @var File\Setting $instance */
        $instance = $this->from('Setting', $locale);
        return $instance;
    }

    public function fromTopic(string $locale = 'de'): File\Topic
    {
        /** @var File\Topic $instance */
        $instance = $this->from('Topic', $locale);
        return $instance;
    }
}
