<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class OfficesListService
{
    public function getOfficesList(): OfficeList|array
    {
        return $this->getOffices();
    }

    private function getOffices(): array|OfficeList
    {
        return ZmsApiFacadeService::getOffices();
    }
}