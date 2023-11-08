<?php

namespace BO\Zmsapi\Tests;

class SourceUpdateTest extends Base
{
    protected $classname = "SourceUpdate";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render([], [
            '__body' => $this->readFixture('GetSource.json'),
            'resolveReferences' => 2
        ], []);
        $entity = (new \BO\Zmsclient\Result($response))->getEntity();
        $this->assertStringContainsString('source.json', (string)$response->getBody());
        $this->assertStringContainsString('"source":"unittest"', (string)$response->getBody());
        $this->assertStringContainsString('providers', (string)$response->getBody());
        $this->assertStringContainsString('requests', (string)$response->getBody());
        $this->assertEquals(1, $entity['requestrelation'][0]['slots']);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithRequestRelation()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render([], [
            '__body' => $this->readFixture('GetSourceWithRequestRelation.json'),
            'resolveReferences' => 2
        ], []);
        $entity = new \BO\Zmsentities\Metaresult(json_decode((string)$response->getBody(), 1));
        $this->assertEquals(21334, $entity['data']['providers'][0]['id']);
        $this->assertEquals(120335, $entity['data']['requests'][0]['id']);
        $this->assertEquals(3, $entity['data']['requestrelation'][0]['slots']);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testUnvalidSchema()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
                "providerList": [
                    {
                        "id": 21334,
                        "name": "Bürgeramt Mitte",
                        "source": "dldb"
                    }
                ]
            }',
        ], []);
    }

    public function testNoLogin()
    {
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingLogin');
        $this->render([], [
            '__body' => $this->readFixture('GetSource.json')
        ], []);
    }

    public function testNoRights()
    {
        $this->setWorkstation()->getUseraccount()->setRights('scope');
        $this->expectException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render([], [
            '__body' => $this->readFixture('GetSource.json')
        ], []);
    }
}
