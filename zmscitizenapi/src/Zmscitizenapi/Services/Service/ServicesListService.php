<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Service;

use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class ServicesListService
{
    public function getServicesList(): ServiceList|array
    {
        return $this->getServices();
    }

    private function getServices(): array|ServiceList
    {
        return ZmsApiFacadeService::getServices();
    }
}
