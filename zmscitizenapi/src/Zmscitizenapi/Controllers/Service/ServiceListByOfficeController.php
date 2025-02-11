<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Service;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Service\ServiceListByOfficeService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServiceListByOfficeController extends BaseController
{
    use UnpublishedAccessTrait;

    private ServiceListByOfficeService $service;
    private bool $showUnpublished;

    public function __construct()
    {
        $this->service = new ServiceListByOfficeService();
        $this->initializeUnpublishedAccess();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }

        $result = $this->service->getServiceList($request->getQueryParams(), $this->showUnpublished);
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
