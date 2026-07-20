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
        foreach ($officeIds as $officeIdRaw) {
            $officeId = (int) $officeIdRaw;
            if ($officeId <= 0) {
                continue;
            }

            try {
                $scope = $this->zmsApiFacadeService->getScopeByOfficeId($officeId);
                if (($scope->captchaActivatedRequired ?? false) === true) {
                    return true;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return false;
    }

    private function isCaptchaRequiredForOfficeId(?int $officeId): bool
    {
        if ($officeId === null || $officeId <= 0) {
            return false;
        }

        return $this->isCaptchaRequiredForOfficeIds([(string) $officeId]);
    }
}
