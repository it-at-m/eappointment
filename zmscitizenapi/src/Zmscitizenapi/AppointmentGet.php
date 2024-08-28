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
            $process = $this->getProcessFromZmsApi($processId, $authKey);

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

    protected function getProcessFromZmsApi($processId, $authKey)
    {
        $resolveReferences = 2;
        $process = \App::$http->readGetResult("/process/{$processId}/{$authKey}/", [
            'resolveReferences' => $resolveReferences
        ])->getEntity();

        return $process;
    }

    protected function getThinnedProcessData($myProcess)
    {
        if (!$myProcess || !isset($myProcess->id)) {
            return [];
        }

        $subRequestCounts = [];
        $mainServiceId = null;
        $mainServiceCount = 0;

        if (isset($myProcess->requests)) {
            $requests = is_array($myProcess->requests) ? $myProcess->requests : iterator_to_array($myProcess->requests);

            if (count($requests) > 0) {
                $mainServiceId = $requests[0]->id;

                foreach ($requests as $request) {
                    if ($request->id === $mainServiceId) {
                        $mainServiceCount++;
                    } else {
                        if (!isset($subRequestCounts[$request->id])) {
                            $subRequestCounts[$request->id] = [
                                'id' => $request->id,
                                'count' => 0
                            ];
                        }
                        $subRequestCounts[$request->id]['count']++;
                    }
                }
            }
        }

        return [
            'processId' => $myProcess->id,
            'timestamp' => isset($myProcess->appointments[0]) ? $myProcess->appointments[0]->date : null,
            'authKey' => $myProcess->authKey ?? null,
            'familyName' => isset($myProcess->clients[0]) ? $myProcess->clients[0]->familyName : null,
            'customTextfield' => $myProcess->customTextfield ?? null,
            'email' => isset($myProcess->clients[0]) ? $myProcess->clients[0]->email : null,
            'telephone' => isset($myProcess->clients[0]) ? $myProcess->clients[0]->telephone : null,
            'officeName' => $myProcess->scope->contact->name ?? null,
            'officeId' => $myProcess->scope->provider->id ?? null,
            'scope' => $myProcess->scope ?? null,
            'subRequestCounts' => array_values($subRequestCounts),
            'serviceId' => $mainServiceId,
            'serviceCount' => $mainServiceCount
        ];
    }

    private function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
}
