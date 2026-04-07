<?php

namespace BO\Zmsstatistic\Tests;

class WorkstationSelectTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationSelect";

    /**
     * Request URI must contain "select" so Access::isPathWithoutScope() is false and
     * WorkstationSelect is not treated like a route that requires an assigned scope.
     */
    private function selectPageParameters(array $extra = []): array
    {
        return array_merge(['__uri' => '/workstation/select/'], $extra);
    }

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/owner/',
                    'response' => $this->readFixture("GET_owner_23.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, $this->selectPageParameters(), []);
        $this->assertStringContainsString('Standort auswählen', (string)$response->getBody());
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginFailed()
    {
        $this->expectException('\BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = "BO\Zmsentities\Exception\UserAccountMissingLogin";
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->selectPageParameters(), []);
    }

    public function testRenderingSelect()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/owner/',
                    'response' => $this->readFixture("GET_owner_23.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->selectPageParameters([
            'scope' => 141
        ]), [], 'POST');
        $this->assertRedirect($response, '/overview/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSelectFailedValidation()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/owner/',
                    'response' => $this->readFixture("GET_owner_23.json")
                ],
            ]
        );
        $response = $this->render($this->arguments, $this->selectPageParameters([
            'scope' => ''
        ]), [], 'POST');
        $this->assertStringContainsString('has-error', (string)$response->getBody());
        $this->assertStringContainsString('Bitte wählen Sie einen Standort aus', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
