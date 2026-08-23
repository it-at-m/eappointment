<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository\Mail;

use BO\Zmscitizenbackend\Exceptions\EmailRequired;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdHydrator;
use BO\Zmscitizenbackend\Repository\Mail\MailQueueRepository;
use BO\Zmscitizenbackend\Tests\Helper\AppointmentByIdRows;
use PHPUnit\Framework\TestCase;

class MailQueueRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        MailQueueRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(MailQueueRepository::class);
        MailQueueRepository::use($override);
        $this->assertSame($override, MailQueueRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(MailQueueRepository::class);
        $override->method('queueConfirmationMail');
        MailQueueRepository::use($override);
        MailQueueRepository::use(null);

        $created = MailQueueRepository::create();
        $this->assertInstanceOf(MailQueueRepository::class, $created);
        $this->assertNotSame($override, $created);
    }

    public function testQueueConfirmationMailThrowsWhenEmailRequiredAndMissing(): void
    {
        $row = AppointmentByIdRows::processRow();
        $row['email'] = '';
        $row['scope_email_required'] = 1;
        $appointment = (new AppointmentByIdHydrator())->hydrate($row, AppointmentByIdRows::requestRows());

        $this->expectException(EmailRequired::class);
        MailQueueRepository::create()->queueConfirmationMail($appointment);
    }

    public function testQueueConfirmationMailSkipsWhenEmailMissingAndNotRequired(): void
    {
        $this->expectNotToPerformAssertions();
        $row = AppointmentByIdRows::processRow();
        $row['email'] = '';
        $row['scope_email_required'] = 0;
        $appointment = (new AppointmentByIdHydrator())->hydrate($row, AppointmentByIdRows::requestRows());

        MailQueueRepository::create()->queueConfirmationMail($appointment);
    }
}
