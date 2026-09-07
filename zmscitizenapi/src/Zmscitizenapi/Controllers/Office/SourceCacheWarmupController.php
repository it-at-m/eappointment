<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Office;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\Office\OfficesServicesRelationsService;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Proactively refresh the offices-and-services source cache in place (ZMSKVR-1191).
 */
class SourceCacheWarmupController extends BaseController
{
    private const WARMUP_TOKEN_HEADER = 'X-Source-Cache-Warmup-Token';

    private OfficesServicesRelationsService $service;

    /** @psalm-api */
    public function __construct()
    {
        $this->service = new OfficesServicesRelationsService();
    }

    #[\Override]
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $expectedToken = getenv('SOURCE_CACHE_WARMUP_TOKEN');
        if (!is_string($expectedToken) || $expectedToken === '') {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('notFound')]],
                ErrorMessages::get('notFound')['statusCode']
            );
        }

        $providedToken = $request->getHeaderLine(self::WARMUP_TOKEN_HEADER);
        if ($providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('unauthorized')]],
                ErrorMessages::get('unauthorized')['statusCode']
            );
        }

        $warmup = $this->service->warmOfficesAndServicesCache();
        $result = $warmup['result'];

        if (is_array($result) && isset($result['errors'])) {
            return $this->createJsonResponse(
                $response,
                $result,
                ErrorMessages::getHighestStatusCode($result['errors'])
            );
        }

        return $this->createJsonResponse($response, [
            'warmed' => true,
            'refreshedKeys' => $warmup['refreshedKeys'],
        ], 200);
    }
}
