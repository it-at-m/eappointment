<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Captcha;

use BO\Zmscitizenbackend\Repository\OfficesServicesRelationsRepository;

trait CaptchaRequirementTrait
{
    private function isCaptchaRequiredForOfficeIds(array $officeIds): bool
    {
        return OfficesServicesRelationsRepository::create()->isCaptchaRequiredForOfficeIds($officeIds);
    }

    private function isCaptchaRequiredForOfficeId(?int $officeId): bool
    {
        if ($officeId === null || $officeId <= 0) {
            return false;
        }

        return $this->isCaptchaRequiredForOfficeIds([(string) $officeId]);
    }
}
