<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Availability\Service;

use BO\Zmscitizenbackend\Core\Service\ValidationService;

trait ServiceLocationValidationTrait
{
    private function validateServiceLocations(array $officeIds, array $serviceIds, bool $showUnpublished = false): ?array
    {
        foreach ($officeIds as $officeId) {
            $errors = ValidationService::validateServiceLocationCombination(
                (int) $officeId,
                array_map('intval', $serviceIds),
                $showUnpublished
            );
            if (!empty($errors['errors'])) {
                return $errors;
            }
        }

        return null;
    }
}
