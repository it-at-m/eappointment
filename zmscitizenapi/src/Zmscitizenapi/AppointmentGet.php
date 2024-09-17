<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppointmentGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $processId = $request->getQueryParams()['processId'] ?? null;
        $authKey = $request->getQueryParams()['authKey'] ?? null;

        $errors = [];

        if (!$processId || !is_numeric($processId) || intval($processId) <= 0) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'processId should be a 32-bit integer',
                'path' => 'processId',
                'location' => 'query'
            ];
        }

        if (!$authKey || !is_string($authKey)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'authKey should be a string',
                'path' => 'authKey',
                'location' => 'query'
            ];
        }

        if (!empty($errors)) {
            $responseContent = ['errors' => $errors];
            return $this->createJsonResponse($response, $responseContent, 400);
        }

        try {
            $process = $this->getProcessById($processId, $authKey);

            if (!$process) {
                $responseContent = [
                    'errorMessage' => 'Termin wurde nicht gefunden',
                ];
                return $this->createJsonResponse($response, $responseContent, 404);
            }

            $responseData = $this->getThinnedProcessData($process);

            return $this->createJsonResponse($response, $responseData, 200);

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'kein Termin gefunden') !== false) {
                $responseContent = [
                    'errorMessage' => 'Termin wurde nicht gefunden',
                ];
                return $this->createJsonResponse($response, $responseContent, 404);
            } else {
                $responseContent = [
                    'error' => 'Unexpected error: ' . $e->getMessage()
                ];
                return $this->createJsonResponse($response, $responseContent, 500);
            }
        }
    }
    
    private function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
}
