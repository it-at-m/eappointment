<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\Services\Core\ExceptionService;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Localization\ErrorMessages;

abstract class BaseController extends \BO\Slim\Controller
{
    protected ?string $language = null;
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $request = $this->initRequest($request);
            $this->language = $request->getAttribute('language');
            ValidationService::setLanguageContext($this->language);
            ExceptionService::setLanguageContext($this->language);
            ZmsApiFacadeService::setLanguageContext($this->language);
            $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
            return $this->readResponse($request, $noCacheResponse, $args);
        } catch (\RuntimeException $e) {
        // Extract error details from the exception message
            [$errorCode, $errorMessage] = explode(': ', $e->getMessage(), 2);
            return $this->createJsonResponse($response, [
                'errors' => [
                    [
                        'errorCode' => $errorCode,
                        'errorMessage' => $errorMessage,
                        'statusCode' => $e->getCode()
                    ]
                ]
            ], $e->getCode() ?: 500);
        }
    }

    protected function getExceptionContext(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return str_replace('Controller', '', $className);
    }

    /**
     * Hook method for handling responses in child controllers.
     * Child classes should override this method to implement their specific response logic.
     *
     * @param RequestInterface $request The HTTP request
     * @param ResponseInterface $response The HTTP response
     * @param array $args Route parameters
     * @return ResponseInterface The modified response
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return parent::__invoke($request, $response, $args);
    }

    protected function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException('Invalid HTTP status code');
        }

        $response = $response->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        if (isset($content['errors'])) {
            foreach ($content['errors'] as &$error) {
                if (isset($error['errorCode'])) {
                    $error = ErrorMessages::get($error['errorCode'], $this->language);
                }
            }
        }

        try {
            $json = json_encode($content, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode JSON response: ' . $e->getMessage(), 0, $e);
        }

        $response->getBody()->write($json);
        return $response;
    }
}
