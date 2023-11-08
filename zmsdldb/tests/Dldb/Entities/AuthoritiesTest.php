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
        $authorityList = $access->fromAuthority()->fetchList();
        
        $this->assertTrue($authorityList->hasLocationId(LOCATION_SINGLE));
        $this->assertFalse($authorityList->removeLocation(LOCATION_SINGLE)->hasLocationId(LOCATION_SINGLE));
        $authorityList = $access->fromAuthority()->fetchList(SERVICE_SINGLE);
        $this->assertTrue($authorityList->hasAppointments());
        $authorityList = $access->fromAuthority()->fetchList(SERVICE_SINGLE.",305303"); //Aufenthaltserlaubnis Praktikum
        // TODO according to rewrite in 76d93352fa0654c4b152c265bb90c2f359205434 this does not work any more
        #var_dump($authorityList);
        #$this->assertTrue($authorityList->hasAppointments());
        #$this->assertFalse($authorityList->hasAppointments(305303));
        #$this->assertTrue($authorityList->hasAppointments(305303, true));

        #$authorityList = $authorityList->removeLocationsWithoutAppointments();
        #$this->assertFalse($authorityList->hasAppointments(305303, true));
    }
}
