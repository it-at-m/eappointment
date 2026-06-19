<?php
namespace BO\Zmsbackend\Tests\RequestVariant\Service;

use BO\Zmsbackend\RequestVariant\Service\RequestVariant;

class RequestVariantTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testReadAllReturnsSortedList(): void
    {
        $repo = new \BO\Zmsbackend\RequestVariant\Service\RequestVariant();
        $out  = $repo->readAll();

        $expected = [
            ['id' => 1, 'name' => 'A – Abmeldung'],
            ['id' => 2, 'name' => 'B – Anmeldung'],
            ['id' => 3, 'name' => 'C – Änderungsmeldung'],
        ];

        $this->assertSame($expected, $out);
    }
}
