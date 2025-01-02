<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesServicesRelations extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $result = $this->getServicesAndOffices();
            
            if (!empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $this->createJsonResponse($response, $result->toArray(), 200);
            
        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('internalError')]],
                500
            );
        }
    }

    private function getServicesAndOffices(): mixed
    {
        return ZmsApiFacadeService::getServicesAndOffices();
    }
}