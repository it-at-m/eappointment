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
        $access = new FileAccess();
        $access->loadFromPath(FIXTURES);
        $locationList = $access->fetchLocationList(SERVICE_SINGLE);

        $this->assertTrue($locationList instanceof \BO\Dldb\Collection\Locations);
        $this->assertTrue(in_array(LOCATION_SINGLE, $locationList->getIds()));
        $this->assertTrue(in_array(LOCATION_SINGLE, explode(',', $locationList->getCSV())));
        $this->assertTrue(in_array(
            LOCATION_SINGLE,
            $locationList->getLocationsWithAppointmentsFor(SERVICE_SINGLE)->getIds()
        ));
        $locationList = $access->fromLocation()->fetchList(305303); //Aufenthaltserlaubnis Praktikum
        $this->assertTrue(in_array(121885, $locationList->getIds()));
        $this->assertTrue(in_array(121885, $locationList->getLocationsWithAppointmentsFor(305303, true)->getIds()));
        $this->assertFalse(in_array(121885, $locationList->getLocationsWithAppointmentsFor(305303)->getIds()));
    }
}
