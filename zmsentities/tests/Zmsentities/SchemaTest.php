<?php

namespace BO\Zmsentities\Tests;

class SchemaTest extends Base
{
    public function testLoader()
    {
        $loader = new \BO\Zmsentities\Schema\Loader();
        try {
            $loader->asJson(false);
            $this->fail("Expected exception SchemaMissingJsonFile not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaMissingJsonFile $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
        try {
            $loader->asArray('../tests/schema/empty.json');
            $this->fail("Expected exception SchemaFailedParseJsonFile not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaFailedParseJsonFile $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    public function testEntity()
    {
        $entity = new \BO\Zmsentities\Department();
        $data = $this->getTestEntityMapping();
        $entity->exchangeArray($data);
        $entity->setResolveLevel(5);

        $this->assertTrue('Berlin' == $entity->contact['city'], 'Schema Helper exchangeArray  failed');
        $this->assertTrue($entity->testValid(), 'Schema Helper testValid failed');
        $this->assertStringContainsString('$schema', $entity->__toString(), 'Schema Helper __toString failed ($schema not found)');
        $this->assertEquals(5, $entity->getResolveLevel());
    }

    public function testCreateExample()
    {
        $entity = new \BO\Zmsentities\Department();
        $entity = $entity->createExample();
        $this->assertTrue('Germany' == $entity->contact['country'], 'Schema Helper createExample failed');
        //no example
        $entity = new \BO\Zmsentities\Ics();
        $entity = $entity->createExample();
        $this->assertTrue(0 == count($entity), 'Schema Helper getExample from entity without example failed');
    }

    public function testJsonSerialize()
    {
        $entity = new \BO\Zmsentities\Department();
        $jsonSerialize = json_decode(json_encode($entity));
        $this->assertTrue($jsonSerialize == $entity->jsonSerialize(), "Schema Helper jsonSerialize does not match");
    }

    public function testResolveLevel()
    {
        $entity = new \BO\Zmsentities\Process([
            'scope' => new \BO\Zmsentities\Scope(['id' => 1234]),
        ]);
        $entity->setResolveLevel(1);
        $this->assertTrue($entity->scope['id'] == 1234, "Scope should be present at resolveLevel=1");
        $this->assertEquals(1, $entity->getResolveLevel());
        $entity = $entity->withResolveLevel(0);
        $this->assertFalse(isset($entity->scope['id']), "Scope should not be present at resolveLevel=0");
        $this->assertEquals(0, $entity->getResolveLevel());
    }

    protected function getTestEntityMapping()
    {
        return [
            'contact__city' => 'Berlin',
            'contact__street' => 'Zaunstraße',
            'contact__country' => 'Germany',
            'contact__name' => 'Flughafen Schönefeld, Landebahn',
            'email' => 'terminvereinbarung@mitte.berlin.de',
            'id' => '1234',
            'name' => 'Flughafen Schönefeld, Landebahn',
            'preferences__notifications__enabled' => true,
            'preferences__notifications__identification' => 'terminvereinbarung@mitte.berlin.de',
            'preferences__notifications__sendConfirmationEnabled' => true,
            'preferences__notifications__sendReminderEnabled' => true
        ];
    }
}
