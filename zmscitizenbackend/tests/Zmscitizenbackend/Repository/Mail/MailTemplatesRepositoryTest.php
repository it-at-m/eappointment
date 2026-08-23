<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository\Mail;

use BO\Zmscitizenbackend\Repository\Mail\MailTemplatesRepository;
use PHPUnit\Framework\TestCase;

class MailTemplatesRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        MailTemplatesRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(MailTemplatesRepository::class);
        MailTemplatesRepository::use($override);
        $this->assertSame($override, MailTemplatesRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(MailTemplatesRepository::class);
        $override->method('readMergedTemplatesForProvider')->willReturn(['icsappointment.twig' => 'BEGIN:VCALENDAR']);
        MailTemplatesRepository::use($override);
        MailTemplatesRepository::use(null);

        $created = MailTemplatesRepository::create();
        $this->assertInstanceOf(MailTemplatesRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
