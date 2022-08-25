<?php

namespace BO\Zmsapi\Tests;

class ProcessListSummaryMailTest extends Base
{
    protected $classname = "ProcessListSummaryMail";

    public function testRendering()
    {
        $response = $this->render([], ['mail' => 'zms@service.berlinonline.de', 'limit' => 3], []);
        $this->assertStringContainsString('Sie haben folgende Termine geplant', (string)$response->getBody());
        $this->assertStringContainsString('10118', (string)$response->getBody());
        $this->assertStringContainsString('10114', (string)$response->getBody());
        $this->assertStringContainsString('10030', (string)$response->getBody());

        $this->assertStringContainsString('am Dienstag, 19. April 2016 um 17:40 Uhr', (string)$response->getBody());
        $this->assertStringContainsString('am Dienstag, 26. April 2016 um 14:20 Uhr', (string)$response->getBody());
        $this->assertStringContainsString('am Montag, 16. Mai 2016 um 08:10 Uhr', (string)$response->getBody());

        $this->assertTrue(200 == $response->getStatusCode());
        return $response;
    }

    public function testProcessListEmpty()
    {
        $this->expectException('BO\Zmsentities\Exception\ProcessListEmpty');
        $this->render([], ['mail' => 'test@unit.test'], []);
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
