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
        $access = new FileAccess(LOCATION_JSON, SERVICE_JSON);
        $authorityList = $access->fetchAuthorityList([SERVICE_SINGLE]);
        $this->assertTrue($authorityList->hasAppointments());
        $authorityList = $access->fetchAuthorityList([SERVICE_SINGLE, 305303]); //Aufenthaltserlaubnis Praktikum
        $this->assertTrue($authorityList->hasAppointments());
        $this->assertFalse($authorityList->hasAppointments(305303));
        $this->assertTrue($authorityList->hasAppointments(305303, true));

    }
}
