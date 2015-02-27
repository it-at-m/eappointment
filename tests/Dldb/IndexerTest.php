<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
 
namespace BO\Dldb\Tests;

use BO\Dldb\FileAccess;

class IndexerTest extends \PHPUnit_Framework_TestCase
{

    public function testIndexer()
    {
        $indexer = new \BO\Dldb\Indexer\ElasticSearch(LOCATION_JSON, SERVICE_JSON);
        $indexer->setHost(ES_HOST);
        $indexer->setPort(ES_PORT);
        $indexer->setTransport(ES_TRANSPORT);
        $indexer->run();
        $indexer->setAlias(ES_ALIAS);
        $indexer->dropOldIndex();
    }

    public function testAccess()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $location = $access->fetchLocation(122281);
        $this->assertNotFalse($location);
        $this->assertArrayHasKey('name', $location);
        $service = $access->fetchService(120703);
        $this->assertNotFalse($service);
        $locationList = $access->fetchLocationList(120703);
        $this->assertArrayHasKey(122281, $locationList);
        $serviceList = $access->fetchServiceList(122281);
        $this->assertArrayHasKey(120703, $serviceList);
        $authorityList = $access->fetchAuthorityList([120703]);
        $this->assertArrayHasKey(12675, $authorityList);
        $serviceList = $access->fetchServiceFromCsv(120703);
        $this->assertArrayHasKey(120703, $serviceList);
        $locationList = $access->fetchLocationFromCsv(122281);
        $this->assertArrayHasKey(122281, $locationList);
    }

    public function testFail()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $location = $access->fetchLocation(1);
        $this->assertFalse($location);
        $service = $access->fetchService(1);
        $this->assertFalse($service);
    }
}
