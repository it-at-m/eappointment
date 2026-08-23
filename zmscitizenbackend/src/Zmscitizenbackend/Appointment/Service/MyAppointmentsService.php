<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Service;

use BO\Zmscitizenbackend\Core\Model\AuthenticatedUser;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Appointment\Repository\MyAppointmentsRepository;

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
