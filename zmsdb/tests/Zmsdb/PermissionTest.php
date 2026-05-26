<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Permission as Query;

class PermissionTest extends Base
{
    public function testReadAllPermissionsDefaultOrder()
    {
        $query = new Query();
        $list = $query->readAllPermissions();

        $this->assertEntityList('\\BO\\Zmsentities\\Permission', $list);
        $this->assertGreaterThan(0, count($list));

        $names = [];
        foreach ($list as $permission) {
            $names[] = (string) $permission->name;
        }
        $sorted = $names;
        sort($sorted, SORT_STRING);
        $this->assertSame($sorted, $names, 'Default order should be ascending by name');
    }

    public function testReadAllPermissionsDescending()
    {
        $query = new Query();
        $list = $query->readAllPermissions('DESC');

        $this->assertEntityList('\\BO\\Zmsentities\\Permission', $list);

        $names = [];
        foreach ($list as $permission) {
            $names[] = (string) $permission->name;
        }
        $sortedAsc = $names;
        sort($sortedAsc, SORT_STRING);
        $expectedDesc = array_reverse($sortedAsc);
        $this->assertSame($expectedDesc, $names, 'DESC order should reverse ascending name order');
    }
}
