<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Tests\Entities;

use BO\Dldb\FileAccess;

class LocationsTest extends Base
{
    public function testFilter()
    {
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $locationList = $access->fetchLocationList(SERVICE_SINGLE);
        $this->assertTrue($locationList instanceof \BO\Dldb\Collection\Locations);
        $this->assertContains(LOCATION_SINGLE, $locationList->getIds());
        $this->assertContains(LOCATION_SINGLE, explode(',', $locationList->getCSV()));
        $this->assertContains(
            LOCATION_SINGLE,
            $locationList->getLocationsWithAppointmentsFor(SERVICE_SINGLE)->getIds()
        );
        $locationList = $access->fetchLocationList(305303); //Aufenthaltserlaubnis Praktikum
        $this->assertContains(121885, $locationList->getIds());
        $this->assertContains(121885, $locationList->getLocationsWithAppointmentsFor(305303, true)->getIds());
        $this->assertNotContains(121885, $locationList->getLocationsWithAppointmentsFor(305303)->getIds());
    }
}
