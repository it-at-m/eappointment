<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Scope;

use BO\Zmscitizenapi\Models\Collections\ThinnedScopeList;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class ScopesListService
{
    public function getScopesList(): ThinnedScopeList|array
    {
        return $this->getScopes();
    }

    private function getScopes(): array|ThinnedScopeList
    {
        return ZmsApiFacadeService::getScopes();
    }
}