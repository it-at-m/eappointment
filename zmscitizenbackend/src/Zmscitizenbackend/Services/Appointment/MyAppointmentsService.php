<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Appointment;

use BO\Zmscitizenbackend\Models\AuthenticatedUser;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\MyAppointmentsRepository;

class MyAppointmentsService
{
    /**
     * @return list<ThinnedProcess>
     */
    public function getAppointmentsForUser(AuthenticatedUser $user, ?int $filterId = null): array
    {
        return MyAppointmentsRepository::create()->readAppointmentsForUser(
            (string) $user->getExternalUserId(),
            $filterId,
            'confirmed'
        );
    }
}
