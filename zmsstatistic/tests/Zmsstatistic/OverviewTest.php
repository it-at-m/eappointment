<?php

namespace BO\Zmsstatistic\Tests;

class OverviewTest extends Base
{
    protected $classname = "Overview";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls(
            [
              [
                  'function' => 'readGetResult',
                  'url' => '/workstation/',
                  'parameters' => ['resolveReferences' => 3],
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
                  'function' => 'readGetResult',
                  'url' => '/warehouse/waitingscope/141/',
                  'response' => $this->readFixture("GET_waitingscope_141.json")
              ],
              [
                  'function' => 'readGetResult',
                  'url' => '/warehouse/clientscope/141/',
                  'response' => $this->readFixture("GET_clientscope_141.json")
              ],
              [
                  'function' => 'readGetResult',
                  'url' => '/warehouse/requestscope/141/',
                  'response' => $this->readFixture("GET_requestscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/overview'], [ ]);
        $this->assertStringContainsString('Übersicht verfügbarer Statistik', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/report/client/scope/2016/">2016</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertStringContainsString('href="/warehouse/">Übersicht Kategorien</a>', (string) $response->getBody());
    }
}
