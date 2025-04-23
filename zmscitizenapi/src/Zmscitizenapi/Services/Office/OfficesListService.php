<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class OfficesListService
{
    public function getOfficesList(bool $showUnpublished = false): OfficeList|array
    {
        return $this->getOffices($showUnpublished);
    }

    private function getOffices(bool $showUnpublished = false): array|OfficeList
    {
        return ZmsApiFacadeService::getOffices($showUnpublished);
    }
}
