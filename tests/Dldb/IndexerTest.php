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
        if (!getenv('FASTTEST')) {
            $indexer = new \BO\Dldb\Indexer\ElasticSearch(LOCATION_JSON, SERVICE_JSON);
            $indexer->setHost(ES_HOST);
            $indexer->setPort(ES_PORT);
            $indexer->setTransport(ES_TRANSPORT);
            $indexer->run();
            $indexer->setAlias(ES_ALIAS);
            $indexer->dropOldIndex();
        }
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
        if (!getenv('FASTTEST')) {
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

    public function testSearch()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $serviceList = $access->searchService('');
        $this->assertTrue($this->listContains($serviceList, 'Personalausweis'), 'Did not find "Personalausweis"');
        $serviceList = $access->searchService('', SERVICE_SINGLE);
        $this->assertTrue($this->listContains($serviceList, 'Reisepass'), 'Did not find "Reisepass"');
        $serviceList = $access->searchService('', SERVICE_SINGLE, LOCATION_CSV);
        $this->assertTrue($this->listContains($serviceList, 'Reisepass'), 'Did not find "Reisepass"');
        $serviceList = $access->searchService('Personalausweis');
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList, 'Did not find ID for "Personalausweis"');
        $serviceList = $access->searchService('Perso');
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList, 'Did not find ID for "Personalausweis"');
        $locationList = $access->searchLocation('');
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau"'
        );
        $locationList = $access->searchLocation('', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau"'
        );
        $locationList = $access->searchLocation('Spandau', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau"'
        );
        $locationList = $access->searchLocation('Bürgeramt', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau"'
        );
        //$this->listNames($locationList);
    }

    protected function listNames($list, $subkey = 'name')
    {
        echo "List with " . count($list) . " items:\n";
        foreach ($list as $key => $item) {
            echo "$key => " . $item[$subkey] ."\n";
        }
    }

    protected function listContains($list, $value, $subkey = 'name')
    {
        foreach ($list as $item) {
            if (array_key_exists($subkey, $item) && false !== strpos($item[$subkey], $value)) {
                return true;
            }
            if (array_key_exists('locations', $item)) {
                foreach ($item['locations'] as $location) {
                    if (array_key_exists($subkey, $location) && false !== strpos($location[$subkey], $value)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
