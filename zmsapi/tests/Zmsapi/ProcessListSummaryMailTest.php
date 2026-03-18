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

        // Diagnose: kommt 10118 überhaupt in der confirmed-Liste vor, wenn wir mehr ziehen?
        $repo = new \BO\Zmsdb\Process();
        $confirmedBig = $repo->readListByMailAndStatusList(
            'zms@service.berlinonline.de',
            [\BO\Zmsentities\Process::STATUS_CONFIRMED],
            2,
            300
        );

        error_log('CONFIRMED IDs (limit=300): ' . implode(',', $confirmedBig->getIds()));
        self::assertContains(10118, $confirmedBig->getIds(), '10118 fehlt schon bei limit=300');

        $repo = new \BO\Zmsdb\Process();

        $confirmed3 = $repo->readListByMailAndStatusList(
            'zms@service.berlinonline.de',
            [\BO\Zmsentities\Process::STATUS_CONFIRMED],
            2,
            3
        );

        $confirmed300 = $repo->readListByMailAndStatusList(
            'zms@service.berlinonline.de',
            [\BO\Zmsentities\Process::STATUS_CONFIRMED],
            2,
            300
        );

        error_log('CONFIRMED IDs (limit=3): ' . implode(',', $confirmed3->getIds()));
        error_log('CONFIRMED IDs (limit=300): ' . implode(',', $confirmed300->getIds()));

        // Erwartung für LIMIT/ORDER-Artefakt: 10118 ist in 300 drin, aber nicht in 3
        self::assertContains(10118, $confirmed300->getIds(), '10118 fehlt bei limit=300');

        // Wenn das hier TRUE wird, ist das sehr starkes Indiz für ORDER/LIMIT-Problematik
        if (!in_array(10118, $confirmed3->getIds(), true) && in_array(10118, $confirmed300->getIds(), true)) {
            self::assertTrue(true);
        } else {
            // Nicht zwingend Fehler, aber dann liegt es eher nicht am LIMIT
            error_log('Hinweis: Unterschied limit=3 vs limit=300 zeigt sich nicht wie erwartet.');
        }

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
