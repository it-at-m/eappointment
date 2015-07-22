<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Indexer;

use BO\Dldb\FileAccess;

/**
  * Index DLDB data into ElasticSearch
  *
  */
class ElasticSearch
{
    const ES_INDEX_PREFIX = 'dldb-';
    const ES_INDEX_DATE = 'Ymd-His';

    /**
      * Access to DLDB files
      *
      * @var FileAccess $dldb
      */
    protected $dldb;

    /**
      * hostname for ES
      *
      * @var String $host
      */
    protected $host = 'localhost';

    /**
      * port for ES
      *
      * @var String $port
      */
    protected $port = '9200';

    /**
      * transport method for ES
      *
      * @var String $transport
      */
    protected $transport = 'Http';

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
     * Due to backward compatibility, the first parameter has two possible meanings
     *
     * @param String $importDirOrLocationFile
     * @param String $servicesFile (optional)
     */
    public function __construct($importOrLocationFile, $servicesFile = null)
    {
        if (is_dir($importOrLocationFile)) {
            $this->dldb = new FileAccess();
            $this->dldb->loadFromPath($importOrLocationFile);
        } elseif (is_file($importOrLocationFile)) {
            $this->dldb = new FileAccess($importOrLocationFile, $servicesFile);
        } else {
            throw new Exception("Invalid import parameters for ElasticSearch indexer");
        }
    }

    /**
     * @return self
     */
    public function run()
    {
        $this->indexServices();
        $this->indexLocations();
        return $this;
    }

    /**
     * @return self
     */
    protected function indexServices()
    {
        $esType = $this->getIndex()->getType('service');
        $docs = array();
        foreach ($this->dldb->fetchServiceList() as $service) {
            $id = $service['meta']['locale'] . $service['id'];
            $docs[] = new \Elastica\Document($id, $service);
        }
        $esType->addDocuments($docs);
        return $this;
    }

    /**
     * @return self
     */
    protected function indexLocations()
    {
        $esType = $this->getIndex()->getType('location');
        $docs = array();
        foreach ($this->dldb->fetchLocationList() as $location) {
            $id = $location['meta']['locale'] . $location['id'];
            $docs[] = new \Elastica\Document($id, $location);
        }
        $esType->addDocuments($docs);
        return $this;
    }

    /**
     * @return \Elastica\Client
     */
    protected function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = new \Elastica\Client(
                array(
                    'host' => $this->host,
                    'port' => $this->port,
                    'transport' => $this->transport
                )
            );
        }
        return $this->connection;
    }

    /**
     * @return \Elastica\Index
     */
    protected function getIndex()
    {
        if (null === $this->index) {
            $connection = $this->getConnection();
            $this->index = $connection->getIndex(self::ES_INDEX_PREFIX . date(self::ES_INDEX_DATE));
            if (!$this->index->exists()) {
                $indexSettings = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'ElasticSearch_Index.json');
                $indexSettings = json_decode($indexSettings, true);
                $this->index->create($indexSettings);
            }
        }
        return $this->index;
    }

    /**
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return self
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * refresh index and add alias
     *
     * @return self
     */
    public function setAlias($alias)
    {
        $this->getIndex()->refresh();
        $this->getIndex()->addAlias($alias, true);
        return $this;
    }

    /**
     * Drop all old indice with the prefix ES_INDEX_PREFIX and no alias
     *
     * @return self
     */
    public function dropOldIndex()
    {
        $client = $this->getConnection();
        $status = $client->getStatus();
        $indexList = $status->getIndexNames();
        $currentIndex = $this->getIndex()->getName();
        foreach ($indexList as $index) {
            if ($currentIndex != $index && 0 === strpos($index, self::ES_INDEX_PREFIX)) {
                $candidateIndex = $client->getIndex($index);
                if (!$candidateIndex->getStatus()->getAliases()) {
                    $candidateIndex->delete();
                }
            }
        }
        return $this;
    }
}
