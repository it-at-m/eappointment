<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb;

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
    public function __construct(mixed $index = null, string $host = 'localhost', string $port = '9200', string $transport = 'Http')
    {
        if ($index) {
            $this->connectElasticSearch($index, $host, $port, $transport);
        }
    }

    public function connectElasticSearch(mixed $index, string $host = 'localhost', string $port = '9200', string $transport = 'Http'): void
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
     * @psalm-api
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
    #[\Override]
    public function loadLocations(mixed $locationJson, string $locale = 'de')
    {
        $this->accessInstance[$locale]['Location'] = new Elastic\Location($locationJson, $locale);
        $this->accessInstance[$locale]['Location']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    #[\Override]
    public function loadServices(mixed $serviceJson, string $locale = 'de')
    {
        $this->accessInstance[$locale]['Service'] = new Elastic\Service($serviceJson, $locale);
        $this->accessInstance[$locale]['Service']->setAccessInstance($this);
        return $this;
    }

    /**
     *
     * @return self
     */
    #[\Override]
    public function loadTopics(mixed $topicJson, string $locale = 'de')
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
    #[\Override]
    public function loadSettings(mixed $settingsJson)
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
    #[\Override]
    public function loadAuthorities(mixed $authorityJson, string $locale = 'de')
    {
        $this->accessInstance[$locale]['Authority'] = new Elastic\Authority($authorityJson, $locale);
        $this->accessInstance[$locale]['Authority']->setAccessInstance($this);
        return $this;
    }
}
