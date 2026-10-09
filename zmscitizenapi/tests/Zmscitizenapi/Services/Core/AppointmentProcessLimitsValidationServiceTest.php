<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Models\ThinnedScope;
use BO\Zmscitizenapi\Services\Core\AppointmentProcessLimitsValidationService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmsentities\Appointment;
use BO\Zmsentities\Process;
use BO\Zmsentities\Scope;
use PHPUnit\Framework\TestCase;

class AppointmentProcessLimitsValidationServiceTest extends TestCase
{
    public function testValidateSlotLimit(): void
    {
        $this->assertSame(
            ['errors' => [ErrorMessages::get('processInvalid')]],
            AppointmentProcessLimitsValidationService::validateSlotLimit(null, '2')
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('processInvalid')]],
            AppointmentProcessLimitsValidationService::validateSlotLimit(0, '2')
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateSlotLimit(2, '2')
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateSlotLimit(2, null)
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateSlotLimit(3, '2')
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateSlotLimit(26, null)
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateSlotLimit(3, '0')
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
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateServiceQuantityLimits(
                [1080502 => 2],
                [1080502 => 0]
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
            [1064268 => 3],
            AppointmentProcessLimitsValidationService::buildServiceCountMap([1064268, 1064268], [1, 2])
        );

        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateProcessLimits(7, '8', [1064268 => 2], 0)
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateProcessLimits(9, '8', [1064268 => 2], 0)
        );
    }

    public function testValidateThinnedProcessLimits(): void
    {
        $allowed = new ThinnedProcess(
            processId: 1,
            authKey: 'fb43',
            familyName: 'Muster',
            email: 'muster@mailinator.com',
            scope: new ThinnedScope(slotsPerAppointment: '8'),
            slotCount: 7,
            status: 'reserved'
        );
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateThinnedProcessLimits($allowed)
        );

        $tooMany = new ThinnedProcess(
            processId: 1,
            authKey: 'fb43',
            familyName: 'Muster',
            email: 'muster@mailinator.com',
            scope: new ThinnedScope(slotsPerAppointment: '8'),
            slotCount: 9,
            status: 'reserved'
        );
        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateThinnedProcessLimits($tooMany)
        );
    }

    public function testValidateReserveLimits(): void
    {
        $process = new Process([
            'scope' => new Scope([
                'id' => 70,
                'preferences' => [
                    'client' => [
                        'slotsPerAppointment' => '8',
                    ],
                ],
            ]),
            'appointments' => [
                new Appointment([
                    'date' => time(),
                    'slotCount' => 9,
                ]),
            ],
        ]);

        $this->assertSame(
            ['errors' => [ErrorMessages::get('tooManySlotsPerAppointment')]],
            AppointmentProcessLimitsValidationService::validateReserveLimits(
                $process,
                [1064268],
                [1],
                0
            )
        );

        $process->getFirstAppointment()->slotCount = 6;
        $this->assertSame(
            ['errors' => []],
            AppointmentProcessLimitsValidationService::validateReserveLimits(
                $process,
                [1064268],
                [1],
                0
            )
        );
    }
}
