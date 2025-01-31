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
    private bool $showUnpublished;

    public function __construct()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } else {
            $domain = $_SERVER['HTTP_HOST'] ?? '';
        }

        $this->service = new OfficesServicesRelationsService();
        $showUnpublishedOnDomain = App::getAccessUnpublishedOnDomain();
        $this->showUnpublished = !empty($showUnpublishedOnDomain)
            && strpos($domain, $showUnpublishedOnDomain) !== false;

    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $requestErrors = ValidationService::validateServerGetRequest($request);
        if (!empty($requestErrors['errors'])) {
            return $this->createJsonResponse(
                $response,
                $requestErrors,
                ErrorMessages::get('invalidRequest', $this->language)['statusCode']
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