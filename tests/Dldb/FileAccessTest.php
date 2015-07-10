<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
 
namespace BO\Dldb\Tests;

use BO\Dldb\FileAccess;

class FileTest extends \PHPUnit_Framework_TestCase
{

    public function testIds()
    {
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON, TOPICS_JSON);
        $access->loadAuthorities(AUTHORITY_JSON);
        $access->loadSettings(SETTINGS_JSON);
        $location = $access->fetchLocation(LOCATION_SINGLE);
        $this->assertNotFalse($location);
        $this->assertArrayHasKey('name', $location);
        $service = $access->fetchService(SERVICE_SINGLE);
        $this->assertNotFalse($service);
        $locationList = $access->fetchLocationList(SERVICE_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $serviceList = $access->fetchServiceList(LOCATION_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $authorityList = $access->fetchAuthorityList([SERVICE_SINGLE]);
        $this->assertArrayHasKey(12675, $authorityList);
        $serviceList = $access->fetchServiceFromCsv(SERVICE_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $locationList = $access->fetchLocationFromCsv(LOCATION_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $results = $access->searchLocation('Spandau', SERVICE_CSV);
        $this->assertTrue(count($results) > 0, "No locations found");
        $results = $access->searchService('Pass', LOCATION_CSV);
        $this->assertTrue(count($results) > 0, "No services found");

        $topicList = $access->fetchTopicList();
        $this->assertTrue(count($topicList) > 0, "No topics found");
        $topic = $access->fetchTopic(TOPIC_SINGLE);
        $this->assertNotFalse($topic);
        $topic = $access->fetchTopicPath('wirtschaft');
        $this->assertNotFalse($topic);

        $borough = $access->fetchBoroughId(13);
        $this->assertEquals('Berlin Gesamt', $borough['name']);

        $eaid = $access->fetchSettingName('ea_id');
        $this->assertTrue($eaid > 100000);

        $authorityList = $access->fetchAuthorityList();
        $this->assertArrayHasKey('contact', $authorityList[AUTHORITY_SINGLE]);
    }

    public function testFail()
    {
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $location = $access->fetchLocation(1);
        $this->assertFalse($location);
        $service = $access->fetchService(1);
        $this->assertFalse($service);
    }

    public function testFailLocation()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        $access = new FileAccess('dummy', 'dummy');
        $access->fromLocation()->loadData();
    }

    public function testFailService()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        $access = new FileAccess(LOCATION_JSON, 'dummy');
        $access->fromService()->loadData();
    }

    public function testFailJsonLocation()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        $access = new FileAccess(__FILE__, 'dummy');
        $access->fromLocation()->loadData();
    }

    public function testFailJsonService()
    {
        $this->setExpectedException("\BO\Dldb\Exception");
        $access = new FileAccess(LOCATION_JSON, __FILE__);
        $access->fromService()->loadData();
    }
}
