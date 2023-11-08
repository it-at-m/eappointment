<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
 
namespace BO\Dldb\Tests;

use BO\Dldb\FileAccess;

class FileAccessTest extends \PHPUnit\Framework\TestCase
{

    public function testIds()
    {
        $access = new FileAccess();
        $access->loadFromPath(FIXTURES);
        $location = $access->fromLocation()->fetchId(LOCATION_SINGLE);
        $this->assertNotFalse($location);
        $this->assertArrayHasKey('name', $location);
        $service = $access->fromService()->fetchId(SERVICE_SINGLE);
        $this->assertNotFalse($service);
        $locationList = $access->fromLocation()->fetchList(SERVICE_CSV);
        $this->assertArrayHasKey(LOCATION_SINGLE, $locationList);
        $serviceList = $access->fromService()->fetchList(LOCATION_CSV);
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $authorityList = $access->fromAuthority()->fetchList(SERVICE_SINGLE);
        $this->assertArrayHasKey(12675, $authorityList);
        $serviceList = $access->fromService()->fetchFromCsv(SERVICE_CSV);
        
        $this->assertArrayHasKey(SERVICE_SINGLE, $serviceList);
        $locationList = $access->fromLocation()->fetchFromCsv(LOCATION_CSV);
        $this->assertArrayHasKey(explode(',', LOCATION_CSV)[0], $locationList);
        $results = $access->fromLocation()->readSearchResultList('Spandau', SERVICE_CSV);
        $this->assertTrue(count($results) > 0, "No locations found");
        $results = $access->fromService()->readSearchResultList('Pass', LOCATION_CSV);
        $this->assertTrue(count($results) > 0, "No services found");

        $topicList = $access->fromTopic()->fetchList();
        $this->assertTrue(count($topicList) > 0, "No topics found");
        $topic = $access->fromTopic()->fetchId(TOPIC_SINGLE);
        $this->assertNotFalse($topic);
        $topic = $access->fromTopic()->fetchPath('wirtschaft');
        $this->assertNotFalse($topic);

        $borough = $access->fromBorough()->fetchId(13);
        $this->assertEquals('Berlin Gesamt', $borough['name']);

        $eaid = $access->fromSetting()->fetchName('ea_id');
        $this->assertTrue($eaid > 100000);

        $authorityList = $access->fromAuthority()->fetchList();
        $this->assertArrayHasKey('contact', $authorityList[AUTHORITY_SINGLE]);

        $office = $access->fromOffice()->fetchPath('auslandsamt');
        $this->assertArrayHasKey('name', $office);
        $this->assertEquals($office['name'], 'Ausländerbehörde');
        $officeList = $access->fromOffice()->fetchList();
        $this->assertArrayHasKey('auslandsamt', $officeList);

        $authorityList = $access->fromAuthority()->readListByOfficePath('auslandsamt');
        $this->assertArrayHasKey(12760, $authorityList);

        $translatedLocation = $access->fromLocation('en')->fetchId(121885);
        $this->assertEquals('en', $translatedLocation['meta']['locale']);
    }

    public function testCompatibility()
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
        $authorityList = $access->fetchAuthorityList(SERVICE_SINGLE);
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
        $topic = $access->fromTopic()->fetchPath('wirtschaft');
        $this->assertNotFalse($topic);
        $topic = $access->fromTopic()->fetchPath('dummy');
        $this->assertFalse($topic);

        $borough = $access->fetchBoroughId(13);
        $this->assertEquals('Berlin Gesamt', $borough['name']);

        $eaid = $access->fetchSettingName('ea_id');
        $this->assertTrue($eaid > 100000);

        $authorityList = $access->fetchAuthorityList();
        $this->assertArrayHasKey('contact', $authorityList[AUTHORITY_SINGLE]);

        $office = $access->fetchOffice('auslandsamt');
        $this->assertArrayHasKey('name', $office);
        $this->assertEquals($office['name'], 'Ausländerbehörde');

        $topic = $access->fromTopic()->fetchPath('verbraucherschutz');
        $serviceList = $access->fromService()->fetchListFromTopic($topic);
        $this->assertArrayHasKey(324330, $serviceList);
        $this->assertArrayHasKey(324330, $serviceList);
        $topic = $access->fromTopic()->fetchPath('tiefbau');
        $serviceList = $access->fromService()->fetchListFromTopic($topic);
        $this->assertArrayHasKey(324501, $serviceList);
    }

    public function testFail()
    {
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $location = $access->fetchLocation(1);
        $this->assertFalse($location);
        $service = $access->fetchService(1);
        $this->assertFalse($service);
    }

    public function testFailInit()
    {
        $this->expectException("\BO\Dldb\Exception");
        $access = new FileAccess();
        $access->fromLocation();
    }

    public function testFailFunction()
    {
        $this->expectException("\BO\Dldb\Exception");
        $access = new FileAccess();
        $access->fetchDummyAction();
    }

    public function testFailLocation()
    {
        $this->expectException("\BO\Dldb\Exception");
        $access = new FileAccess('dummy', 'dummy');
        $access->fromLocation()->loadData();
    }

    public function testFailService()
    {
        $this->expectException("\BO\Dldb\Exception");
        $access = new FileAccess(LOCATION_JSON, 'dummy');
        $access->fromService()->loadData();
    }

    public function testFailJsonLocation()
    {
        $this->expectException("\BO\Dldb\Exception");
        $access = new FileAccess(__FILE__, 'dummy');
        $access->fromLocation()->loadData();
    }

    public function testFailJsonService()
    {
        $this->expectException("\BO\Dldb\Exception");
        $access = new FileAccess(LOCATION_JSON, __FILE__);
        $access->fromService()->loadData();
    }
}
