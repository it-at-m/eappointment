<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Office;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenapi\Helper\ErrorMessages;
use BO\Zmscitizenapi\Services\Office\OfficesListService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesListController extends BaseController
{
    use UnpublishedAccessTrait;

    private OfficesListService $service;
    private bool $showUnpublished;

    public function __construct()
    {
        $this->service = new OfficesListService();
        $this->initializeUnpublishedAccess();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest', $this->language)['statusCode']);
        }

        $result = $this->service->getOfficesList($this->showUnpublished);
        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse($response, $result, ErrorMessages::getHighestStatusCode($result['errors']))
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
