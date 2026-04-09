<?php

namespace BO\Zmsentities\Tests;

/**
 * @SuppressWarnings(Public)
 */
class RoleTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Role';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertSame(8, $entity->id);
        $this->assertSame('system_admin', $entity->name);
        $this->assertSame('Full access to all system features', $entity->description);
        $this->assertSame(['superuser'], $entity->permissions);
    }

    public function testGetDefaults()
    {
        $entity = new $this->entityclass();
        $defaults = $entity->getDefaults();
        $this->assertSame([], $defaults['permissions']);
    }
}
