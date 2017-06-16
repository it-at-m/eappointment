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
        $this->assertContains('$schema', $entity->__toString(), 'Schema Helper __toString failed ($schema not found)');
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

    public function testTestValid()
    {
        $entity = new \BO\Zmsentities\Useraccount();
        $entity->id= 123;
        $entity->changePassword= array('test', 'testfailed');
        try {
            $entity->testValid();
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testJsonSerialize()
    {
        $entity = new \BO\Zmsentities\Department();
        $jsonSerialize = json_decode(json_encode($entity));
        $this->assertTrue($jsonSerialize == $entity->jsonSerialize(), "Schema Helper jsonSerialize does not match");
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
