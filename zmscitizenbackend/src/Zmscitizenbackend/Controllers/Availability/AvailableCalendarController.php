<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Controllers\Availability;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Controllers\UnpublishedAccessTrait;
use BO\Zmscitizenbackend\Services\Availability\AvailableCalendarService;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailableCalendarController extends BaseController
{
    use UnpublishedAccessTrait;

    private AvailableCalendarService $service;

    /** @psalm-api */
    public function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->initializeUnpublishedAccess();
        $this->service = new AvailableCalendarService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse($response, $requestErrors, ErrorMessages::get('invalidRequest')['statusCode']);
        }

        $queryParams = $request->getQueryParams();
        $result = $this->service->getAvailableCalendar($queryParams, $this->showUnpublished);

        $isError = is_array($result) && isset($result['errors']);
        return $this->createJsonResponse(
            $response,
            $isError ? $result : $result->toArray(),
            $isError ? ErrorMessages::getHighestStatusCode($result['errors']) : 200
        );
    }
}
