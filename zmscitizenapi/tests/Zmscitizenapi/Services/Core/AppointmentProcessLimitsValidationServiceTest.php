<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Services\Core\AppointmentProcessLimitsValidationService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use PHPUnit\Framework\TestCase;

class AppointmentProcessLimitsValidationServiceTest extends TestCase
{
    public function testValidateAppointmentSlotLimit(): void
    {
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateAppointmentSlotLimit(null, '2')
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateAppointmentSlotLimit(0, '2')
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateAppointmentSlotLimit(2, '2')
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateAppointmentSlotLimit(2, null)
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateAppointmentSlotLimit(3, '2')
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateAppointmentSlotLimit(26, null)
        );
    }

    public function testValidateServiceQuantityLimits(): void
    {
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateServiceQuantityLimits(
                [1080502 => 1],
                [1080502 => 2]
            )
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateServiceQuantityLimits(
                [1080502 => 2],
                [1080502 => null]
            )
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManyServicesPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateServiceQuantityLimits(
                [1080502 => 3],
                [1080502 => 2]
            )
        );
    }

    public function testBuildServiceCountMapAndProcessLimitsWithoutOfficeLookup(): void
    {
        $this->assertSame(
            [1064268 => 2, 1064374 => 1],
            AppointmentProcessLimitsValidationService::buildServiceCountMap([1064268, 1064374], [2, 1])
        );

        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateAppointmentProcessLimits(
                7,
                '8',
                [1064268 => 2],
                0
            )
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateAppointmentProcessLimits(
                9,
                '8',
                [1064268 => 2],
                0
            )
        );
    }
}
