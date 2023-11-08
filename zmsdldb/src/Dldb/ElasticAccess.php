<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
 * @SuppressWarnings(TooManyMethods)
 *
 * Using elastica query classes increases object dependencies dramatically
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ElasticAccess extends FileAccess
{

    /**
     * The client used to talk to elastic search.
     *
     * @var \Elastica\Client
     */
    protected $connection;

    /**
      * Index from elastic search
      *
      * @var \Elastica\Index $index
      */
    protected $index;

    /**
     *
     * @return self
     */
    public function __construct($index = null, $host = 'localhost', $port = '9200', $transport = 'Http')
    {
        if ($index) {
            $this->connectElasticSearch($index, $host, $port, $transport);
        }
    }

    public function connectElasticSearch($index, $host = 'localhost', $port = '9200', $transport = 'Http')
    {
        $this->connection = new \Elastica\Client(array(
                'host' => $host,
                'port' => $port,
                'transport' => $transport
        ));
        $this->index = $this->getConnection()->getIndex($index);
    }

    /**
     *
     * @return \Elastica\Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     *
     * @return \Elastica\Client
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     *
     * @return self
     */
    public function loadLocations($locationJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Location'] = new Elastic\Location($locationJson, $locale);
        $this->accessInstance[$locale]['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadServices($serviceJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Service'] = new Elastic\Service($serviceJson, $locale);
        $this->accessInstance[$locale]['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadTopics($topicJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Topic'] = new Elastic\Topic($topicJson, $locale);
        $this->accessInstance[$locale]['Topic']->setAccessInstance($this);
        $this->accessInstance[$locale]['Link'] = new Elastic\Link($topicJson, $locale);
        $this->accessInstance[$locale]['Link']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $this->accessInstance['de']['Setting'] = new Elastic\Setting($settingsJson);
        $this->accessInstance['de']['Setting']->setAccessInstance($this);
        $this->accessInstance['de']['Office'] = new Elastic\Office($settingsJson);
        $this->accessInstance['de']['Office']->setAccessInstance($this);
        $this->accessInstance['de']['Borough'] = new Elastic\Borough($settingsJson);
        $this->accessInstance['de']['Borough']->setAccessInstance($this);
        return $this;
    }

  
    /**
     *
     * @return self
     */
    public function loadAuthorities($authorityJson, $locale = 'de')
    {
        $this->accessInstance[$locale]['Authority'] = new Elastic\Authority($authorityJson, $locale);
        $this->accessInstance[$locale]['Authority']->setAccessInstance($this);
        return $this;
    }
}
