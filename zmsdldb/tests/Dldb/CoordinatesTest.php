<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Tests;

use BO\Dldb\Plz\Coordinates;

class CoordinatesTest extends \PHPUnit\Framework\TestCase
{
    public function testCoordinates()
    {
        $plz = new Coordinates();
        $result = $plz->getLatLon(13585);
        $result2 = Coordinates::zip2LatLon(13585);
        $this->assertArrayHasKey('lat', $result);
        $this->assertArrayHasKey('lon', $result);
        $this->assertTrue($result['lat'] > 52);
        $this->assertTrue($result['lon'] > 13);
        $this->assertEquals($result, $result2);
        $doesnotexists = $plz->getLatLon(2);
        $this->assertFalse($doesnotexists);
    }

    public function testFail()
    {
        $this->expectException("\Exception");
        new Coordinates("/dummy/notexists.json");
    }
}
