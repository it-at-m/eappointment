<?php

namespace BO\Zmsentities\Tests;

/**
 * @SuppressWarnings(Public)
 */
class PermissionTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Permission';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEntity($this->entityclass, $entity);
        $this->assertSame(1, $entity->id);
        $this->assertSame('appointment', $entity->name);
        $this->assertStringContainsString('appointments', (string) $entity->description);
    }
}
