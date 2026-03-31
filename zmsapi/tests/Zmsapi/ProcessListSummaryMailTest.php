<?php

namespace BO\Zmsapi\Tests;

use Fig\Http\Message\StatusCodeInterface;

class ProcessListSummaryMailTest extends Base
{
    protected $classname = "ProcessListSummaryMail";

    public function setUp(): void
    {
        parent::setUp();
        if (getenv('SKIP_DNS_VALIDATION') === '1') {
            // Mark this test class as skipped when DNS validation cannot work locally
            $this->markTestSkipped('Skipping DNS-dependent ProcessListSummaryMailTest locally');
        }
    }

    public function testRendering()
    {
        $entity = (new \BO\Zmsdb\Process)->readEntity(10118, new \BO\Zmsdb\Helper\NoAuth);
        $oldStatus = $entity->status;
        $entity->status = 'confirmed';
        (new \BO\Zmsdb\Process)->updateEntity($entity, \App::getNow());

        $response = $this->render([], ['mail' => 'zms@service.berlinonline.de', 'limit' => 3], []);
        self::assertStringContainsString('Sie haben folgende Termine gebucht', (string)$response->getBody());
        self::assertStringContainsString('10118', (string)$response->getBody());

        self::assertStringContainsString('am Dienstag, 19. April 2016 um 17:40 Uhr', (string)$response->getBody());

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->testShortRepetitionFailure();
        $this->testShortRepetitionSuccess();

        $entity->status = $oldStatus;
        (new \BO\Zmsdb\Process)->updateEntity($entity, \App::getNow());
    }

    private function testShortRepetitionFailure()
    {
        $this->expectException('BO\Zmsapi\Exception\Process\ProcessListSummaryTooOften');
        $response = $this->render([], ['mail' => 'zms@service.berlinonline.de', 'limit' => 3], []);
        self::assertSame(StatusCodeInterface::STATUS_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    private function testShortRepetitionSuccess()
    {
        \App::$now->modify("+ 10Minutes");
        $response = $this->render([], ['mail' => 'zms@service.berlinonline.de', 'limit' => 3], []);
        self::assertStringContainsString('haben Sie folgende Termine gebucht', (string)$response->getBody());
    }

    public function testProcessListEmpty()
    {
        $configRepository = (new \BO\Zmsdb\Config());
        $config = $configRepository->readEntity();
        $config->setPreference('mailings', 'noReplyDepartmentId', '74');
        $configRepository->updateEntity($config);
        $response = $this->render([], ['mail' => 'not.existing@service.berlinonline.de'], []);
        self::assertStringContainsString('Es wurden keine gebuchten Termine gefunden.', (string)$response->getBody());
    }

    public function testUnvalidMail()
    {
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->expectExceptionMessage(
            "Validation failed: no valid email\nno valid DNS entry found\n({mail}=='test@unit')"
        );
        $this->render([], ['mail' => 'test@unit'], []);
    }

    
}
