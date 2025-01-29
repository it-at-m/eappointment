<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class OfficesServicesRelationsService
{
    public function getServicesAndOfficesList(bool $showUnpublished = false): OfficeServiceAndRelationList|array
    {
        return $this->getServicesAndOffices($showUnpublished);
    }

    private function getServicesAndOffices(bool $showUnpublished): array|OfficeServiceAndRelationList
    {
        return ZmsApiFacadeService::getServicesAndOffices($showUnpublished);
    }
}