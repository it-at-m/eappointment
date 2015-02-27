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
        $location = $access->fetchLocation(LOCATION_SINGLE);
        $this->assertNotFalse($location);
        $this->assertArrayHasKey('name', $location);
        $service = $access->fetchService(SERVICE_SINGLE);
        $this->assertNotFalse($service);
        $locationList = $access->fetchLocationList();
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $locationList = $access->fetchLocationList(SERVICE_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $serviceList = $access->fetchServiceList();
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $serviceList = $access->fetchServiceList(LOCATION_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $authorityList = $access->fetchAuthorityList([SERVICE_SINGLE]);
        $this->assertArrayHasKey(12675, $authorityList);
        $serviceList = $access->fetchServiceFromCsv(SERVICE_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $locationList = $access->fetchLocationFromCsv(LOCATION_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $results = $access->searchLocation('Spandau', SERVICE_CSV);
        $this->assertTrue($results > 0, "No locations found");
        $results = $access->searchService('Pass', LOCATION_CSV);
        $this->assertTrue($results > 0, "No services found");
        $results = $access->searchLocation('Spandau');
        $this->assertTrue($results > 0, "No locations found");
        $results = $access->searchService('Pass');
        $this->assertTrue($results > 0, "No services found");
    }

    public function testFail()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $location = $access->fetchLocation(1);
        $this->assertFalse($location);
        $service = $access->fetchService(1);
        $this->assertFalse($service);
    }

    public function testCompare()
    {
        return;
        $access1 = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $access2 = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $location1 = $access1->fetchLocation(LOCATION_SINGLE);
        $location2 = $access2->fetchLocation(LOCATION_SINGLE);
        $this->assertEquals($location1, $location2);
        $service1 = $access1->fetchService(SERVICE_SINGLE);
        $service2 = $access2->fetchService(SERVICE_SINGLE);
        $this->assertEquals($service1, $service2);
        $locationList1 = $access1->fetchLocationList(SERVICE_CSV);
        $locationList2 = $access2->fetchLocationList(SERVICE_CSV);
        $this->assertEquals($locationList1, $locationList2);
        $serviceList1 = $access1->fetchServiceList(LOCATION_CSV);
        $serviceList2 = $access2->fetchServiceList(LOCATION_CSV);
        $this->assertEquals($serviceList1, $serviceList2);
        $authorityList1 = $access1->fetchAuthorityList([SERVICE_SINGLE]);
        $authorityList2 = $access2->fetchAuthorityList([SERVICE_SINGLE]);
        $this->assertEquals($authorityList1, $authorityList2);
        $serviceList1 = $access1->fetchServiceFromCsv(SERVICE_CSV);
        $serviceList2 = $access2->fetchServiceFromCsv(SERVICE_CSV);
        $this->assertEquals($serviceList1, $serviceList2);
        $locationList1 = $access1->fetchLocationFromCsv(LOCATION_CSV);
        $locationList2 = $access2->fetchLocationFromCsv(LOCATION_CSV);
        $this->assertEquals($locationList1, $locationList2);
        // search results might differ and are not compared
    }
}
