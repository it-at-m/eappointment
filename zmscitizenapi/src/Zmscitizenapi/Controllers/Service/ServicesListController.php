<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Service;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Services\Service\ServicesListService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServicesListController extends BaseController
{
    use UnpublishedAccessTrait;

    private ServicesListService $service;
    private bool $showUnpublished;

    public function __construct()
    {
        $this->service = new ServicesListService();
        $this->initializeUnpublishedAccess();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }

        $result = $this->service->getServicesList($this->showUnpublished);
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
