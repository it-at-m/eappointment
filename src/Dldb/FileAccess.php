<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 *
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
    public function loadFromPath($path, $locale = 'de')
    {
        if (!is_dir($path)) {
            throw new Exception("Could not read directory $path");
        }
        $this->loadSettings($path . '/settings.json');
        $this->loadAuthorities($path . DIRECTORY_SEPARATOR . 'authorities_' . $locale . '.json');
        $this->loadLocations($path . DIRECTORY_SEPARATOR . 'locations_' . $locale . '.json');
        $this->loadServices($path . DIRECTORY_SEPARATOR . 'services_' . $locale . '.json');
        $this->loadTopics($path . DIRECTORY_SEPARATOR . 'topics_' . $locale . '.json');
        return $this;
    }

    /**
     * @return self
     */
    public function loadLocations($locationJson)
    {
        $this->accessInstance['Location'] = new File\Location($locationJson);
        $this->accessInstance['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadServices($serviceJson)
    {
        $this->accessInstance['Service'] = new File\Service($serviceJson);
        $this->accessInstance['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadTopics($topicJson)
    {
        $this->accessInstance['Topic'] = new File\Topic($topicJson);
        $this->accessInstance['Topic']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $this->accessInstance['Setting'] = new File\Setting($settingsJson);
        $this->accessInstance['Setting']->setAccessInstance($this);
        $this->accessInstance['Office'] = new File\Office($settingsJson);
        $this->accessInstance['Office']->setAccessInstance($this);
        $this->accessInstance['Borough'] = new File\Borough($settingsJson);
        $this->accessInstance['Borough']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadAuthorities($authorityJson)
    {
        $this->accessInstance['Authority'] = new File\Authority($authorityJson);
        $this->accessInstance['Authority']->setAccessInstance($this);
        return $this;
    }

    /**
     * @todo refactor: returns services, not topics.
     */
    public function fetchTopicServicesList($topic_path)
    {
        trigger_error("Deprecated function fetchTopicServicesList, use fromService()->fetchTopic()");
        return $this->fromService()->fetchTopic($topic_path);
    }

    /**
     * @todo will not work in every edge case, cause authority export does not contain officeinformations
     * @todo returns Collection\Authorities and not locations
     * @return Collection\Locations
     */
    public function fetchLocationListByOffice($officepath = false)
    {
        $authoritylist = $this->authorities;
        if ($officepath) {
            $authoritylist = new Collection\Authorities(array_filter(
                (array)$authoritylist,
                function ($item) use ($officepath) {
                    $authority = new \BO\Dldb\Entity\Authority($item);
                    return $authority->matchLocationWithOffice($officepath);
                }
            ));
        }
        return $authoritylist;
    }
}
