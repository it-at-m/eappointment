<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Appointment;

use BO\Zmscitizenbackend\Services\Core\MapperService;
use BO\Zmscitizenbackend\Models\AuthenticatedUser;
use BO\Zmscitizenbackend\Services\Core\ZmsApiFacadeService;

class MyAppointmentsService
{
    public function getAppointmentsForUser(AuthenticatedUser $user, ?int $filterId = null): array
    {
        $externalUserId = $user->getExternalUserId();
        $processList = ZmsApiFacadeService::getAppointmentsByExternalUserId($externalUserId, $filterId, "confirmed");
        $thinnedProcessList = [];
        foreach ($processList as $process) {
            $thinnedProcessList[] = MapperService::processToThinnedProcess($process);
        }
        return $thinnedProcessList;
    }
}
