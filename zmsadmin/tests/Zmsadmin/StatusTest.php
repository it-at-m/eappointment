<?php

namespace BO\Zmsadmin\Tests;

class StatusTest extends Base
{
    protected $arguments = [];
    protected $parameters = [];

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Superuser_Resolved1.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $body = (string)$response->getBody();
        $this->assertStringContainsString('<th>Version</th>', $body);
        $this->assertStringContainsString('86861', $body);
        $this->assertStringContainsString('Anzahl der aktuell aufgerufenen Termine', $body);
        $this->assertStringContainsString('Anzahl der aktuell geparkten Termine', $body);
        $this->assertStringContainsString('Anzahl der gebuchten Termine mit Bürgerlogin', $body);
        $this->assertStringContainsString('Letzter Import Dienstleistungsdatenbank', $body);
        $this->assertStringContainsString('Anzahl noch nicht versendeter Mails', $body);
        $this->assertStringContainsString('Alter noch nicht versendeter Mails', $body);
        $this->assertStringContainsString('Aktive Sitzungen der Mitarbeitenden', $body);
        $this->assertStringContainsString('Auslastung der Datenbankverbindungen', $body);
        $this->assertStringContainsString('Nur für technische Administration sichtbar', $body);
        $this->assertStringContainsString('Automatische Tests', $body);
        $this->assertStringContainsString('zmsautomation', $body);
        $this->assertStringContainsString('branch=next', $body);
        $this->assertStringNotContainsString('zmsautomation (main)', $body);
    }

    public function testWithoutWorkstation()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\UserAccountMissingLogin';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'exception' => $exception
                ]
            ]
        );
        parent::testRendering();
    }

    public function testWithoutSuperuserPermissionHidesOperationsMetrics()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $body = (string)$response->getBody();
        $this->assertStringContainsString('Anzahl der aktuell aufgerufenen Termine', $body);
        $this->assertStringContainsString('Anzahl der aktuell geparkten Termine', $body);
        $this->assertStringNotContainsString('Anzahl der gebuchten Termine mit Bürgerlogin', $body);
        $this->assertStringNotContainsString('Letzter Import Dienstleistungsdatenbank', $body);
        $this->assertStringNotContainsString('Auslastung der Datenbankverbindungen', $body);
        $this->assertStringNotContainsString('Status des Datenbank-Clusters', $body);
        $this->assertStringNotContainsString('Sekundengenaues Backup', $body);
        $this->assertStringNotContainsString('Alter noch nicht versendeter Mails', $body);
        $this->assertStringNotContainsString('Anzahl noch nicht versendeter Mails', $body);
        $this->assertStringNotContainsString('Aktive Sitzungen der Mitarbeitenden', $body);
        $this->assertStringNotContainsString('Nur für technische Administration sichtbar', $body);
        $this->assertStringNotContainsString('Automatische Tests', $body);
        $this->assertStringNotContainsString('zmsautomation', $body);
    }
}
