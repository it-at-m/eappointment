<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Service;

use BO\Zmscitizenapi\Models\Collections\ServiceList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class ServicesListService
{
    public function getServicesList(bool $showUnpublished = false): ServiceList|array
    {
        return $this->getServices($showUnpublished);
    }

    private function getServices(bool $showUnpublished = false): array|ServiceList
    {
        return ZmsApiFacadeService::getServices($showUnpublished);
    }
}
