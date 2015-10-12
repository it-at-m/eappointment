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
            $indexer = new \BO\Dldb\Indexer\ElasticSearch(FIXTURES);
            $indexer->setHost(ES_HOST);
            $indexer->setPort(ES_PORT);
            $indexer->setTransport(ES_TRANSPORT);
            $indexer->run();
            $indexer->setAlias(ES_ALIAS);
            $indexer->dropOldIndex();
        }
    }

    public function testBasicQuery()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $access->loadFromPath(FIXTURES);
        $location = $access->fromLocation()->fetchId(LOCATION_SINGLE);
        $this->assertNotFalse($location);
    }

    public function testAccess()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $access->loadFromPath(FIXTURES);
        $location = $access->fromLocation()->fetchId(LOCATION_SINGLE);
        $this->assertNotFalse($location);
        $this->assertArrayHasKey('name', $location);
        $service = $access->fromService()->fetchId(SERVICE_SINGLE);
        $this->assertNotFalse($service);
        $locationList = $access->fromLocation()->fetchList();
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $locationList = $access->fromLocation()->fetchList(SERVICE_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $serviceList = $access->fromService()->fetchList();
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $serviceList = $access->fromService()->fetchList(LOCATION_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $authorityList = $access->fromAuthority()->fetchList([SERVICE_SINGLE]);
        $this->assertArrayHasKey(12675, $authorityList);
        $serviceList = $access->fromService()->fetchFromCsv(SERVICE_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $locationList = $access->fromLocation()->fetchFromCsv(LOCATION_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $results = $access->searchLocation('Spandau', SERVICE_CSV);
        $this->assertTrue(count($results) > 0, "No locations found");
        $results = $access->searchService('Pass', LOCATION_CSV);
        $this->assertTrue(count($results) > 0, "No services found");
        $results = $access->searchLocation('Spandau');
        $this->assertTrue(count($results) > 0, "No locations found");
        $results = $access->searchService('Pass');
        $this->assertTrue(count($results) > 0, "No services found");

        $translatedLocation = $access->fromLocation('en')->fetchId(121885);
        $this->assertEquals('en', $translatedLocation['meta']['locale']);
        $translatedService = $access->fromService('en')->fetchId(305244); //Aufenthaltserlaubnis zum Studium
        $this->assertEquals('en', $translatedService['meta']['locale']);
    }

    public function testFail()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $access->loadFromPath(FIXTURES);
        $location = $access->fromLocation()->fetchId(1);
        $this->assertFalse($location);
        $service = $access->fromService()->fetchId(1);
        $this->assertFalse($service);
    }

    public function testCompare()
    {
        if (!getenv('FASTTEST') && !getenv('NOCOMPARE')) {
            $access1 = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
            $access1->loadFromPath(FIXTURES);
            $access2 = new FileAccess();
            $access2->loadFromPath(FIXTURES);
            $location1 = $access1->fromLocation()->fetchId(LOCATION_SINGLE);
            $location2 = $access2->fromLocation()->fetchId(LOCATION_SINGLE);
            $this->assertEquals($location1, $location2);
            $service1 = $access1->fromService()->fetchId(SERVICE_SINGLE);
            $service2 = $access2->fromService()->fetchId(SERVICE_SINGLE);
            $this->assertEquals($service1, $service2);
            $locationList1 = $access1->fromLocation()->fetchList(SERVICE_CSV);
            $locationList2 = $access2->fromLocation()->fetchList(SERVICE_CSV);
            $this->assertEquals($locationList1, $locationList2);
            $serviceList1 = $access1->fromService()->fetchList(LOCATION_CSV);
            $serviceList2 = $access2->fromService()->fetchList(LOCATION_CSV);
            $this->assertEquals($serviceList1, $serviceList2);
            $authorityList1 = $access1->fromAuthority()->fetchList([SERVICE_SINGLE]);
            $authorityList2 = $access2->fromAuthority()->fetchList([SERVICE_SINGLE]);
            $this->assertEquals($authorityList1, $authorityList2);
            $serviceList1 = $access1->fromService()->fetchFromCsv(SERVICE_CSV);
            $serviceList2 = $access2->fromService()->fetchFromCsv(SERVICE_CSV);
            $this->assertEquals($serviceList1, $serviceList2);
            $locationList1 = $access1->fromLocation()->fetchFromCsv(LOCATION_CSV);
            $locationList2 = $access2->fromLocation()->fetchFromCsv(LOCATION_CSV);
            $this->assertEquals($locationList1, $locationList2);
            // search results might differ and are not compared
        }
    }

    public function testSearch()
    {
        $access = new \BO\Dldb\ElasticAccess(ES_ALIAS, ES_HOST, ES_PORT, ES_TRANSPORT);
        $access->loadFromPath(FIXTURES);
        $serviceList = $access->searchService('');
        $this->assertTrue(
            $this->listContains($serviceList, 'Personalausweis'),
            'Did not find "Personalausweis" in * search'
        );
        $serviceList = $access->searchService('', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($serviceList, 'Reisepass'),
            'Did not find "Reisepass" filtered * search'
        );
        $serviceList = $access->searchService('', SERVICE_SINGLE, LOCATION_CSV);
        $this->assertTrue(
            $this->listContains($serviceList, 'Reisepass'),
            'Did not find "Reisepass" in filtered search by location'
        );
        $serviceList = $access->searchService('Personalausweis');
        $this->assertArrayHasKey(
            SERVICE_SINGLE,
            $serviceList,
            'Did not find ID for "Personalausweis" in full keyword search'
        );
        $serviceList = $access->searchService('Perso');
        $this->assertArrayHasKey(
            SERVICE_SINGLE,
            $serviceList,
            'Did not find ID for "Personalausweis" in prefix search'
        );
        $locationList = $access->searchLocation('');
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau" in * search'
        );
        $locationList = $access->searchLocation('', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau" in filtered search'
        );
        $locationList = $access->searchLocation('Spandau', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau" in full keyword search'
        );
        $locationList = $access->searchLocation('Bürgeramt', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau" in search with special german characters'
        );
        $locationList = $access->searchLocation('13589', SERVICE_SINGLE);
        $this->assertTrue(
            $this->listContains($locationList, 'Bürgeramt Rathaus Spandau'),
            'Did not find "Bürgeramt Rathaus Spandau" in search with postal code'
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
