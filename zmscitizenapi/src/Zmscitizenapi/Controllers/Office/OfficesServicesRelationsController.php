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
        $domain = $request->getUri()->getHost();
        $showUnpublished = !empty($this->showUnpublishedOnDomain)
            && strpos($domain, $this->showUnpublishedOnDomain) !== false;
        var_dump('----');
        var_dump($domain);
        var_dump($this->showUnpublishedOnDomain);
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