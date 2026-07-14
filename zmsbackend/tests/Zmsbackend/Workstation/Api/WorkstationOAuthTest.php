<?php

namespace BO\Zmsbackend\Tests\Workstation\Api;

class WorkstationOAuthTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "WorkstationOAuth";

    public static $useraccount = '';

    public static $authKey = 'vorschau';

    public function setUp(): void
    {
        parent::setUp();
        if (empty(static::$useraccount)) {
            static::$useraccount = json_decode($this->readFixture('GetUseraccount.json'), true);
            static::$useraccount['id'] = 'testadmin@keycloak';
            static::$useraccount['username'] = 'testadmin';
            static::$useraccount['email'] = 'testadmin@example.com';
            static::$useraccount = json_encode(static::$useraccount);
        }
    }

    public function testRendering()
    {
        $response = $this->render(
            [],
            [
                '__header' => [
                    'X-AuthKey' => md5(static::$authKey),
                ],
                '__body' => static::$useraccount,
                'nocommit' => 1,
                'state' => md5(static::$authKey),
            ], []
        );

        $this->assertStringContainsString('workstation.json', (string)$response->getBody());
        $this->assertStringContainsString('testadmin', (string)$response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }


    public function testInvalidStateHeaderMismatch()
    {
        $this->expectException(\BO\Zmsbackend\Workstation\Exception\WorkstationAuthFailed::class);

        $this->render(
            [],
            [
                '__header' => [
                    'X-AuthKey' => 'INVALID',
                ],
                '__body' => static::$useraccount,
                'nocommit' => 1,
                'state' => md5(static::$authKey),
            ], []
        );
    }

    public function testMissingStateParam()
    {
        $this->expectException(\BO\Zmsbackend\Workstation\Exception\WorkstationAuthFailed::class);

        $this->render(
            [],
            [
                '__header' => [
                    'X-AuthKey' => md5(static::$authKey),
                ],
                '__body' => static::$useraccount,
                'nocommit' => 1,
            ], []
        );
    }

    public function testNotFound()
    {
        $this->expectException(\BO\Zmsbackend\Useraccount\Exception\UseraccountNotFound::class);

        $data = json_decode($this->readFixture('GetUseraccount_unknown.json'), true);
        $data['id'] = 'unknownuser@keycloak';
        $data['username'] = 'unknown';
        $data['email'] = 'unknown@example.com';
        $body = json_encode($data);

        $this->render(
            [],
            [
                '__header' => [
                    'X-AuthKey' => md5(static::$authKey),
                ],
                '__body' => $body,
                'nocommit' => 1,
                'state' => md5(static::$authKey),
            ], []
        );
    }

    public function testEmpty()
    {
        $this->expectException(\BO\Mellon\Failure\Exception::class);

        $this->render([], [], []);
    }
}
