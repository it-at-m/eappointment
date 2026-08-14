<?php

namespace BO\Zmsadmin\Tests;

class ProfileTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = 'Profile';

    public function testRendering(): void
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture(
                        'GET_Workstation_Resolved1.json'
                    ),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/profile/',
                    'parameters' => [],
                    'response' => $this->readFixture(
                        'GET_workstation_profile.json'
                    ),
                ],
            ]
        );

        $response = $this->render(
            $this->arguments,
            $this->parameters,
            []
        );

        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode());

        $this->assertStringContainsString('Mein Profil', $body);
        $this->assertStringContainsString('testadmin', $body);
        $this->assertStringContainsString(
            'Technische Administration',
            $body
        );
        $this->assertStringContainsString(
            'Alle Funktionen',
            $body
        );
        $this->assertStringContainsString(
            'Termine direkt aus der Warteschlange aufrufen',
            $body
        );
        $this->assertStringContainsString(
            'Warteschlange sehen',
            $body
        );

        $this->assertStringNotContainsString('@keycloak', $body);
        $this->assertStringNotContainsString('E-Mail-Adresse', $body);
        $this->assertStringNotContainsString(
            'Anmeldedaten ändern',
            $body
        );
        $this->assertStringNotContainsString(
            'Zugeordnete Einheiten',
            $body
        );
        $this->assertStringNotContainsString(
            'Passwortwiederholung',
            $body
        );
    }

    public function testProfileApiExceptionIsForwarded(): void
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '';

        $this->expectException(\BO\Zmsclient\Exception::class);

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture(
                        'GET_Workstation_Resolved1.json'
                    ),
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/profile/',
                    'parameters' => [],
                    'exception' => $exception,
                ],
            ]
        );

        $this->render(
            $this->arguments,
            $this->parameters,
            []
        );
    }
}
