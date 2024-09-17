<?php

namespace BO\Zmscitizenapi;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @codeCoverageIgnore
     *
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }

    protected function convertDateToDayMonthYear($dateString)
    {
        $date = new \DateTime($dateString);
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }







    protected function getProcessById($processId, $authKey)
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
}
