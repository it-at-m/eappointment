<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Office\Controller;

use BO\Zmscitizenbackend\BaseController;
use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Office\Service\OfficesServicesRelationsService;
use BO\Zmscitizenbackend\Core\Service\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenbackend\UnpublishedAccessTrait;
use Psr\Container\ContainerInterface;

class OfficesServicesRelationsController extends BaseController
{
    use UnpublishedAccessTrait;

    private OfficesServicesRelationsService $service;
    private bool $showUnpublished;

    /** @psalm-api */
    public function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->service = new OfficesServicesRelationsService();
        $this->initializeUnpublishedAccess();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest')['statusCode']
            );
        }

        $result = $this->service->getServicesAndOfficesList($this->showUnpublished);

        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse(
                $response,
                $result,
                ErrorMessages::getHighestStatusCode($result['errors'])
            )
            : $this->createJsonResponse($response, $result->toArray(), 200);
    }
}
