<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FileAccess extends AbstractAccess
{

    /**
     * Parameters for json files are deprecated, try using loadFromPath() instead
     *
     * @return self
     */
    public function __construct(
        $locationJson = null,
        $serviceJson = null,
        $topicsJson = null,
        $authoritiesJson = null,
        $settingsJson = null
    ) {
        if (null !== $locationJson) {
            $this->loadLocations($locationJson);
        }
        if (null !== $serviceJson) {
            $this->loadServices($serviceJson);
        }
        if (null !== $topicsJson) {
            $this->loadTopics($topicsJson);
        }
        if (null !== $authoritiesJson) {
            $this->loadAuthorities($authoritiesJson);
        }
        if (null !== $settingsJson) {
            $this->loadSettings($settingsJson);
        }
    }

    /**
     * Parameters for json files are deprecated, try using loadFromPath() instead
     *
     * @return self
     */
    public function loadFromPath($path)
    {
        if (!is_dir($path)) {
            throw new Exception("Could not read directory $path");
        }
        $this->loadAuthorities($path . DIRECTORY_SEPARATOR . 'authority_de.json', 'de');
        $this->loadAuthorities($path . DIRECTORY_SEPARATOR . 'authority_de.json', 'en');
        $this->loadLocations($path . DIRECTORY_SEPARATOR . 'locations_de.json', 'de');
        $this->loadLocations($path . DIRECTORY_SEPARATOR . 'locations_en.json', 'en');
        $this->loadServices($path . DIRECTORY_SEPARATOR . 'services_de.json', 'de');
        $this->loadServices($path . DIRECTORY_SEPARATOR . 'services_en.json', 'en');
        $this->loadSettings($path . DIRECTORY_SEPARATOR . 'settings.json');
        $this->loadTopics($path . DIRECTORY_SEPARATOR . 'topic_de.json', 'de');
        $this->loadTopics($path . DIRECTORY_SEPARATOR . 'topic_de.json', 'en');
        return $this;
    }

    public function loadLocationsFromPathByLocale($path, $locale)
    {
        $this->loadLocations($path . DIRECTORY_SEPARATOR . 'locations_' . $locale . '.json', $locale);
    }

    public function loadServicesFromPathByLocale($path, $locale)
    {
        $this->loadServices($path . DIRECTORY_SEPARATOR . 'services_' . $locale . '.json', $locale);
    }

    public function loadTopicsFromPathByLocale($path, $locale)
    {
        $this->loadTopics($path . DIRECTORY_SEPARATOR . 'topic_' . $locale . '.json', $locale);
    }

    public function loadAuthoritiesFromPathByLocale($path, $locale)
    {
        $this->loadAuthorities($path . DIRECTORY_SEPARATOR . 'authority_' . $locale . '.json', $locale);
    }

    public function loadSettingsFromPath($path)
    {
        $this->loadSettings($path . DIRECTORY_SEPARATOR . 'settings.json');
    }

    /**
     *
     * @return self
     */
    public function loadLocations($locationJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Location'] = new File\Location($locationJson, $locale);
        $this->accessInstance[$locale]['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadServices($serviceJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Service'] = new File\Service($serviceJson, $locale);
        $this->accessInstance[$locale]['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadTopics($topicJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Topic'] = new File\Topic($topicJson, $locale);
        $this->accessInstance[$locale]['Topic']->setAccessInstance($this);
        $this->accessInstance[$locale]['Link'] = new File\Link($topicJson, $locale);
        $this->accessInstance[$locale]['Link']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $this->accessInstance['de']['Setting'] = new File\Setting($settingsJson);
        $this->accessInstance['de']['Setting']->setAccessInstance($this);
        $this->accessInstance['de']['Office'] = new File\Office($settingsJson);
        $this->accessInstance['de']['Office']->setAccessInstance($this);
        $this->accessInstance['de']['Borough'] = new File\Borough($settingsJson);
        $this->accessInstance['de']['Borough']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadAuthorities($authorityJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Authority'] = new File\Authority($authorityJson, $locale);
        $this->accessInstance[$locale]['Authority']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @todo refactor: returns services, not topics.
     */
    public function fetchTopicServicesList($topic_path)
    {
        trigger_error("Deprecated function fetchTopicServicesList, use fromService()->fetchTopic()");
        return $this->fromService()->fetchTopicPath($topic_path);
    }

    /**
     *
     * @todo will not work in every edge case, cause authority export does not contain officeinformations
     * @todo returns Collection\Authorities and not locations
     * @return Collection\Locations
     */
    public function fetchLocationListByOffice($officepath = false)
    {
        trigger_error("Deprecated function fetchLocationListByOffice, use fromAuthority()->fetchOffice()");
        return $this->fromAuthority()->fetchOffice($officepath);
    }
}
