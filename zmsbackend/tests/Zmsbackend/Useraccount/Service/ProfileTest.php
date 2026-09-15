<?php

namespace BO\Zmsbackend\Tests\Useraccount\Service;

use BO\Zmsbackend\Useraccount\Exception\UseraccountInvalidRoleAssignment;
use BO\Zmsbackend\Useraccount\Service\Profile as ProfileService;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Useraccountprofile;

class ProfileTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testReadEntity(): void
    {
        $useraccount = new Useraccount([
            'id' => 'testuser@keycloak',
            'roles' => ['agent_queue'],
        ]);

        $profile = (new ProfileService())->readEntity($useraccount);

        $this->assertEntity(Useraccountprofile::class, $profile);
        $this->assertSame('testuser', $profile->username);
        $this->assertSame(
            'Sachbearbeitung (Standard)',
            $profile->role
        );

        $this->assertSame(
            [
                'Abgeschlossene Termine sehen',
                'Geparkte Termine sehen',
                'Kunden suchen (anstehende Termine, keine Termine aus Vergangenheit)',
                'Notruf auslösen/empfangen',
                'Offene Aufrufe sehen',
                'Sachbearbeiteransicht aufrufen + Termine aufrufen/verwalten/vergeben/verschieben/stornieren (inkl. Benachrichtigungsmail senden)',
                'Termine direkt aus der Warteschlange aufrufen (hierfür wird queue benötigt)',
                'Tresenansicht aufrufen (inkl. Monats- und Wochenkalender)',
                'Verpasste Termine sehen',
                'Warteschlange sehen',
            ],
            $profile->permissions
        );
    }

    public function testReadEntityWithoutRoleFails(): void
    {
        $this->expectException(
            UseraccountInvalidRoleAssignment::class
        );

        $useraccount = new Useraccount([
            'id' => 'testuser',
            'roles' => [],
        ]);

        (new ProfileService())->readEntity($useraccount);
    }

    public function testReadEntityWithMultipleRolesFails(): void
    {
        $this->expectException(
            UseraccountInvalidRoleAssignment::class
        );

        $useraccount = new Useraccount([
            'id' => 'testuser',
            'roles' => [
                'agent_basic',
                'agent_queue',
            ],
        ]);

        (new ProfileService())->readEntity($useraccount);
    }

    public function testReadEntityWithUnknownRoleFails(): void
    {
        $this->expectException(
            UseraccountInvalidRoleAssignment::class
        );

        $useraccount = new Useraccount([
            'id' => 'testuser',
            'roles' => ['unknown_role'],
        ]);

        (new ProfileService())->readEntity($useraccount);
    }
}
