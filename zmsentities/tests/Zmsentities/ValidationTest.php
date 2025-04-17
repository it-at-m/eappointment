<?php

namespace BO\Zmsentities\Tests;

class ValidationTest extends Base
{
    protected $testData;
    protected $testSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = [
            'id' => '123',
            'name' => 'Test User'
        ];
        $this->testSchema = new \BO\Zmsentities\Schema\Schema([
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'string'],
                'name' => ['type' => 'string']
            ],
            'required' => ['id', 'name']
        ]);
    }

    public function testTestValid()
    {
        $entity = new \BO\Zmsentities\Useraccount();
        $entity->id= "123";
        $entity->changePassword= array('test', 'testfailed');
        try {
            $entity->testValid();
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testTestValidObject()
    {
        $entity = (new \BO\Zmsentities\Scope())->getExample();
        $entity->preferences['client']['emailFrom'] = "test.de";
        try {
            $entity->testValid();
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            foreach ($exception->data as $error) {
                $error = $exception->data['/preferences/client/emailFrom'];
                $this->assertContainsEquals(
                    'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
                    $error['messages']
                );
            }
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testTestValidObjectReference()
    {
        $entity = (new \BO\Zmsentities\Scope())->getExample();
        $entity['contact']['email'] = "test.de";
        try {
            $entity->testValid('de_DE', 1);
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            foreach ($exception->data as $error) {
                $error = $exception->data['/contact/email'];
                $this->assertContainsEquals(
                    'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
                    $error['messages']
                );
            }
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testLocale()
    {
        $mockCache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
        $validator = new \BO\Zmsentities\Schema\Validator($this->testData, $this->testSchema, 'de', $mockCache);
        $this->assertTrue($validator->isValid());
    }
}
