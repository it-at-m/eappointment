<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

use Psr\Http\Message\ResponseInterface;

class WorkstationProfileGetTest extends
    \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = 'WorkstationProfileGet';

    public function testRendering(): ResponseInterface
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->roles = ['agent_queue'];

        $response = $this->render([], [], []);
        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(
            'useraccountprofile.json',
            $body
        );
        $this->assertStringContainsString(
            'Sachbearbeitung (Standard)',
            $body
        );

        return $response;
    }

    public function testAccessibleForEveryRole(): void
    {
        $workstation = $this->setWorkstation();

        $roleNames = [
            'agent_basic',
            'agent_queue',
            'agent_queue_plus',
            'appointment_admin',
            'reporting_viewer',
            'user_admin',
            'audit_viewer',
            'system_admin',
        ];

        foreach ($roleNames as $roleName) {
            $workstation->getUseraccount()->roles = [$roleName];

            $response = $this->render([], [], []);
            $body = (string) $response->getBody();

            $this->assertSame(
                200,
                $response->getStatusCode(),
                sprintf(
                    'Profil konnte für Rolle "%s" nicht geladen werden.',
                    $roleName
                )
            );

            $this->assertStringContainsString(
                'useraccountprofile.json',
                $body
            );
        }
    }
}
