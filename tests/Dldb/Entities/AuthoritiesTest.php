<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Tests\Entities;

use BO\Dldb\FileAccess;

class AuthoritiesTest extends Base
{
    public function testHasAppointments()
    {
        $access = new FileAccess();
        $access->loadFromPath(FIXTURES);
        $authorityList = $access->fetchAuthorityList();
        $this->assertTrue($authorityList->hasLocationId(LOCATION_SINGLE));
        $this->assertFalse($authorityList->removeLocation(LOCATION_SINGLE)->hasLocationId(LOCATION_SINGLE));
        $authorityList = $access->fetchAuthorityList([SERVICE_SINGLE]);
        $this->assertTrue($authorityList->hasAppointments());
        $authorityList = $access->fetchAuthorityList([SERVICE_SINGLE, 305303]); //Aufenthaltserlaubnis Praktikum
        $this->assertTrue($authorityList->hasAppointments());
        $this->assertFalse($authorityList->hasAppointments(305303));
        $this->assertTrue($authorityList->hasAppointments(305303, true));

        $authorityList = $authorityList->removeLocationsWithoutAppointments();
        $this->assertFalse($authorityList->hasAppointments(305303, true));
    }
}
