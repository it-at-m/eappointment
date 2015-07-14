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
        $this->connection = new \Elastica\Client(
            array(
                'host' => $host,
                'port' => $port,
                'transport' => $transport
            )
        );
        $this->index = $this->getConnection()->getIndex($index);
    }

    /**
     * @return \Elastica\Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return \Elastica\Client
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return self
     */
    public function loadLocations($locationJson)
    {
        $this->accessInstance['Location'] = new Elastic\Location($locationJson);
        $this->accessInstance['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadServices($serviceJson)
    {
        $this->accessInstance['Service'] = new Elastic\Service($serviceJson);
        $this->accessInstance['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadTopics($topicJson)
    {
        $this->accessInstance['Topic'] = new Elastic\Topic($topicJson);
        $this->accessInstance['Topic']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadSettings($settingsJson)
    {
        $this->accessInstance['Setting'] = new Elastic\Setting($settingsJson);
        $this->accessInstance['Setting']->setAccessInstance($this);
        $this->accessInstance['Office'] = new Elastic\Office($settingsJson);
        $this->accessInstance['Office']->setAccessInstance($this);
        $this->accessInstance['Borough'] = new Elastic\Borough($settingsJson);
        $this->accessInstance['Borough']->setAccessInstance($this);
        return $this;
    }

    /**
     * @return self
     */
    public function loadAuthorities($authorityJson)
    {
        $this->accessInstance['Authority'] = new Elastic\Authority($authorityJson);
        $this->accessInstance['Authority']->setAccessInstance($this);
        return $this;
    }
}
