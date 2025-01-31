<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Office;

use App;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Office\OfficesServicesRelationsService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesServicesRelationsController extends BaseController
{
    private OfficesServicesRelationsService $service;
    private ?string $showUnpublishedOnDomain;

    public function __construct()
    {
        $this->service = new OfficesServicesRelationsService();
        $this->showUnpublishedOnDomain = App::getAccessUnpublishedOnDomain();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $uri = $request->getUri()->getScheme();
        var_dump($uri);exit;
        $showUnpublished = !empty($this->showUnpublishedOnDomain)
            && strpos($uri, $this->showUnpublishedOnDomain) !== false;
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest', $this->language)['statusCode']
            );
        }

        $result = $this->service->getServicesAndOfficesList($showUnpublished);

        return is_array($result) && isset($result['errors'])
            ? $this->createJsonResponse(
                $response,
                $result,
                ErrorMessages::getHighestStatusCode($result['errors'])
            )
            : $this->createJsonResponse($response, $result->toArray(), 200);

    }
}