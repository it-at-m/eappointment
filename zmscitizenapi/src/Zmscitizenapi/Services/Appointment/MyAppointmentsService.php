<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Appointment;

use BO\Zmscitizenapi\Services\Core\MapperService;
use BO\Zmscitizenapi\Models\AuthenticatedUser;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

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
