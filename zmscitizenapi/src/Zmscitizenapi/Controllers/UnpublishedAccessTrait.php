<?php

namespace BO\Zmscitizenapi\Controllers;

use App;

trait UnpublishedAccessTrait
{
    private bool $showUnpublished;
    private function initializeUnpublishedAccess(): void
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            $domain = $_SERVER['HTTP_HOST'] ?? '';
        }

        $showUnpublishedOnDomain = App::getAccessUnpublishedOnDomain();
        $this->showUnpublished = !empty($showUnpublishedOnDomain)
            && strpos($domain, $showUnpublishedOnDomain) !== false;
    }
}
