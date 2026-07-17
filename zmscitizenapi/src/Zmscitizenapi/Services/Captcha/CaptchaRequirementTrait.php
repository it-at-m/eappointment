<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Captcha;

/**
 * Requires the using class to define: private ZmsApiFacadeService $zmsApiFacadeService
 */
trait CaptchaRequirementTrait
{
    private function isCaptchaRequiredForOfficeIds(array $officeIds): bool
    {
        return $this->zmsApiFacadeService->isCaptchaRequiredForAnyOffice($officeIds);
    }

    private function isCaptchaRequiredForOfficeId(?int $officeId): bool
    {
        if ($officeId === null || $officeId <= 0) {
            return false;
        }

        return $this->isCaptchaRequiredForOfficeIds([(string) $officeId]);
    }
}
