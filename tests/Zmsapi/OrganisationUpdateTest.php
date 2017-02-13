<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class OrganisationUpdateTest extends Base
{
    protected $classname = "OrganisationUpdate";

    const SCOPE_ID = 143;

    public function testNoRights()
    {
        User::$workstation = new Workstation([
            'id' => '137',
            'useraccount' => new Useraccount([
                'id' => 'testuser',
                'rights' => [
                    'organisation' => false
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $this->setExpectedException('BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->render([54], [
            '__body' => '',
        ], []);
    }

    public function testRendering()
    {
        User::$workstation = new Workstation([
            'id' => '138',
            'useraccount' => new Useraccount([
                'id' => 'berlinonline',
                'rights' => [
                    'organisation' => true,
                    'superuser' => true
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $organisation = new \BO\Zmsentities\Organisation(
            json_decode($this->readFixture("GetOrganisation.json"))
        );
        $organisation->preferences->ticketPrinterProtectionEnabled = 1;
        $response = $this->render([54], [
            '__body' => json_encode($organisation)
        ], []);
        $this->assertContains('organisation.json', (string)$response->getBody());
        $this->assertContains('"ticketPrinterProtectionEnabled":"1"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
