<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

class ClusterCalldisplayImageDataGetTest extends Base
{
    protected $classname = "ClusterCalldisplayImageDataGet";

    const SCOPE_ID = 141;

    const CLUSTER_ID = 109;

    public function testRendering()
    {
        User::$workstation = new Workstation([
            'id' => '138',
            'useraccount' => new Useraccount([
                'id' => 'berlinonline',
                'rights' => [
                    'superuser' => true,
                    'cluster' => true
                ]
            ]),
            'scope' => new Scope([
                'id' => self::SCOPE_ID,
            ])
        ]);
        $response = $this->render([self::CLUSTER_ID], [], []);
        $this->assertContains('mimepart.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $response = $this->render([], [], []);
    }

    public function testClusterNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Cluster\ClusterNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render([999], [], []);
    }
}
