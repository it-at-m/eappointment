<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Availability;

use BO\Zmscitizenapi\Services\Core\ValidationService;

trait ServiceLocationValidationTrait
{
    private function validateServiceLocations(array $officeIds, array $serviceIds): ?array
    {
        foreach ($officeIds as $officeId) {
            $errors = ValidationService::validateServiceLocationCombination(
                (int) $officeId,
                array_map('intval', $serviceIds)
            );
            if (!empty($errors['errors'])) {
                return $errors;
            }
        }

        return null;
    }
}
