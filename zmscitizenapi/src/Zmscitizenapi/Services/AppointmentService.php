<?php

namespace BO\Zmscitizenapi\Services;

use BO\Zmscitizenapi\Services\ProcessService;

class AppointmentService
{
    protected $processService;

    public function __construct(ProcessService $processService)
    {
        $this->processService = $processService;
    }

    public function getAppointmentById($processId, $authKey)
    {
        $errors = $this->validateInputs($processId, $authKey);
        if (!empty($errors)) {
            return ['errors' => $errors, 'status' => 400];
        }

        try {
            $process = $this->processService->getProcessById($processId, $authKey);
            
            if (!$process) {
                return [
                    'errorMessage' => 'Termin wurde nicht gefunden',
                    'status' => 404,
                ];
            }

            $responseData = $this->getThinnedProcessData($process);
            return ['data' => $responseData, 'status' => 200];

        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'kein Termin gefunden') !== false) {
                return [
                    'errorMessage' => 'Termin wurde nicht gefunden',
                    'status' => 404,
                ];
            } else {
                return [
                    'error' => 'Unexpected error: ' . $e->getMessage(),
                    'status' => 500,
                ];
            }
        }
    }

    private function validateInputs($processId, $authKey)
    {
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

        return $errors;
    }

    private function getThinnedProcessData($myProcess)
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
                                'count' => 0,
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
            'serviceCount' => $mainServiceCount,
        ];
    }
}
