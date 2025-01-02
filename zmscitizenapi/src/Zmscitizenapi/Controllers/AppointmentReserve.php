<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Helper\DateTimeFormatHelper;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use BO\Zmscitizenapi\Models\Captcha\FriendlyCaptcha;
use BO\Zmsentities\Process;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentReserve extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $request = $request instanceof ServerRequestInterface ? $request : null;
        $body = $request->getParsedBody();

        $clientData = $this->extractClientData($body);

        $errors = $this->validateClientData($clientData);
        if (!empty($errors['errors'])) {
            $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
            return $this->createJsonResponse($response, $errors, $statusCode);
        }

        try {
            if (!$this->verifyCaptcha($clientData->officeId, $clientData->captchaSolution)) {
                return $this->createJsonResponse($response, ErrorMessages::get('captchaVerificationFailed'), 400);
            }

            $errors = ValidationService::validateServiceLocationCombination(
                $clientData->officeId,
                $clientData->serviceIds
            );
            
            if (!empty($errors['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
                return $this->createJsonResponse($response, $errors, $statusCode);
            }

            $selectedProcess = $this->findMatchingProcess(
                $clientData->officeId,
                $clientData->serviceIds,
                $clientData->serviceCounts,
                $clientData->timestamp
            );

            $errors = ValidationService::validateGetProcessNotFound($selectedProcess);
            if (!empty($errors['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($errors['errors']);
                return $this->createJsonResponse($response, $errors, $statusCode);
            }

            $reservedProcess = $this->reserveAppointment(
                $selectedProcess,
                $clientData->serviceIds,
                $clientData->serviceCounts,
                $clientData->officeId
            );

            return $this->createJsonResponse($response, $reservedProcess, 200);

        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('internalError')]],
                500
            );
        }
    }

    private function extractClientData(array $body): object
    {
        return (object) [
            'officeId' => isset($body['officeId']) && is_numeric($body['officeId']) ? (int) $body['officeId'] : null,
            'serviceIds' => $body['serviceId'] ?? null,
            'serviceCounts' => $body['serviceCount'] ?? [1],
            'captchaSolution' => $body['captchaSolution'] ?? null,
            'timestamp' => isset($body['timestamp']) && is_numeric($body['timestamp']) ? (int) $body['timestamp'] : null,
        ];
    }

    private function validateClientData(object $data): array
    {
        return ValidationService::validatePostAppointmentReserve(
            $data->officeId,
            $data->serviceIds,
            $data->serviceCounts,
            $data->timestamp
        );
    }

    private function verifyCaptcha(?int $officeId, ?string $captchaSolution): bool|array
    {
        $providerScope = ZmsApiFacadeService::getScopeByOfficeId($officeId);
        $captchaRequired = Application::$CAPTCHA_ENABLED === true && 
            isset($providerScope->captchaActivatedRequired) && 
            $providerScope->captchaActivatedRequired === "1";

        if (!$captchaRequired) {
            return true;
        }

        try {
            $captcha = new FriendlyCaptcha();
            return $captcha->verifyCaptcha($captchaSolution);
        } catch (\Exception $e) {
            return ErrorMessages::get('captchaVerificationError');
        }
    }

    private function findMatchingProcess(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        int $timestamp
    ): ?Process {
        $freeAppointments = ZmsApiFacadeService::getFreeAppointments(
            $officeId,
            $serviceIds,
            $serviceCounts,
            DateTimeFormatHelper::getInternalDateFromTimestamp($timestamp)
        );

        foreach ($freeAppointments as $process) {
            if (!isset($process->appointments) || empty($process->appointments)) {
                continue;
            }

            foreach ($process->appointments as $appointment) {
                if ((int)$appointment->date === $timestamp) {
                    $requestIds = [];
                    if ($process->requests) {
                        foreach ($process->requests as $request) {
                            $requestIds[] = $request->getId();
                        }
                    }

                    $processData = [
                        'requests' => $requestIds,
                        'appointments' => [$appointment]
                    ];

                    $process->withUpdatedData(
                        $processData,
                        new \DateTime("@$timestamp"),
                        $process->scope
                    );
                    return $process;
                }
            }
        }

        return null;
    }

    private function reserveAppointment(
        Process $process,
        array $serviceIds,
        array $serviceCounts,
        int $officeId
    ): array {
        $process->clients = [
            [
                'email' => 'test@muenchen.de'
            ]
        ];

        $reservedProcess = ZmsApiFacadeService::reserveTimeslot($process, $serviceIds, $serviceCounts);

        if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
            $scopeId = $reservedProcess->scope->id;
            $scope = ZmsApiFacadeService::getScopeById((int) $scopeId);

            if (!isset($scope['errors']) && isset($scope) && !empty($scope)) {
                $reservedProcess->scope = $scope;
            }
        }

        return array_merge($reservedProcess->toArray(), ['officeId' => $officeId]);
    }
}