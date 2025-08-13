<?php
namespace BO\Zmsdb\Tests;

use BO\Zmsdb\RequestVariant;

class RequestVariantTest extends Base
{
    public function testReadAllReturnsSortedList(): void
    {
        $repo = new RequestVariant();
        $out  = $repo->readAll();

        $expected = [
            ['id' => 1, 'name' => 'A – Abmeldung'],
            ['id' => 2, 'name' => 'B – Anmeldung'],
            ['id' => 3, 'name' => 'C – Änderungsmeldung'],
        ];

        $this->assertCount(count($expected), $out);

        $this->assertSame($expected, $out);

        foreach ($out as $row) {
            $this->assertIsInt($row['id']);
            $this->assertIsString($row['name']);
        }
    }
}
