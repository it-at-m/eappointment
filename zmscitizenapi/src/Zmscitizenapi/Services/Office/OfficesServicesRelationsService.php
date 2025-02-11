<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class OfficesServicesRelationsService
{
    public function getServicesAndOfficesList(): OfficeServiceAndRelationList|array
    {
        return $this->getServicesAndOffices();
    }

    private function getServicesAndOffices(): array|OfficeServiceAndRelationList
    {
        return ZmsApiFacadeService::getServicesAndOffices();
    }
}
