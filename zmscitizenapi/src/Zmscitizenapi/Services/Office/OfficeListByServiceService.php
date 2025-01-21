<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeList;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class OfficeListByServiceService
{
    public function getOfficeList(array $queryParams): OfficeList|array
    {
        $clientData = $this->extractClientData($queryParams);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            return $errors;
        }

        return $this->getOfficeListByService($clientData);
    }

    private function extractClientData(array $queryParams): object
    {
        return (object) [
            'serviceId' => isset($queryParams['serviceId']) && is_numeric($queryParams['serviceId'])
                ? (int) $queryParams['serviceId']
                : null
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validateGetOfficeListByServiceId($data->serviceId);
    }

    private function getOfficeListByService(object $data): array|OfficeList
    {
        return ZmsApiFacadeService::getOfficeListByServiceId($data->serviceId);
    }
}