<?php

namespace BO\Zmscitizenapi;

use \BO\Zmscitizenapi\Application;
use \BO\Zmscitizenapi\BaseController;
use \BO\Zmscitizenapi\Helper\UtilityHelper;
use \BO\Zmscitizenapi\Services\FriendlyCaptchaService;
use \BO\Zmscitizenapi\Services\MapperService;
use \BO\Zmscitizenapi\Services\ValidationService;
use \BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use \BO\Zmscitizenapi\Models\ThinnedProcess;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Scope;
use \BO\Zmsentities\Collection\ProcessList;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentReserve extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $request = $request instanceof ServerRequestInterface ? $request : null;

        $body = $request->getParsedBody();

        $officeId = isset($body['officeId']) && is_numeric($body['officeId']) ? (int) $body['officeId'] : null;
        $serviceIds = $body['serviceId'] ?? null;
        $serviceCounts = $body['serviceCount'] ?? [1];
        $captchaSolution = $body['captchaSolution'] ?? null;
        $timestamp = isset($body['timestamp']) && is_numeric($body['timestamp']) ? (int) $body['timestamp'] : null;

        $errors = ValidationService::validatePostAppointmentReserve($officeId, $serviceIds, $serviceCounts, $timestamp);
        if (!empty($errors['errors'])) {
            return $this->createJsonResponse($response, $errors, 400);
        }

        try {
            $providerScope = ZmsApiFacadeService::getScopeByOfficeId($officeId);
            $captchaRequired = Application::$CAPTCHA_ENABLED === true && isset($providerScope['captchaActivatedRequired']) && $providerScope['captchaActivatedRequired'] === "1";

            if ($captchaRequired) {
                $captchaVerificationResult = FriendlyCaptchaService::verifyCaptcha($captchaSolution);
                if (!$captchaVerificationResult['success']) {
                    return $this->createJsonResponse($response, [
                        'errorCode' => 'captchaVerificationFailed',
                        'errorMessage' => 'Captcha verification failed'
                    ], 400);
                }
            }

            $serviceValidationResult = ValidationService::validateServiceLocationCombination($officeId, $serviceIds);
            if ($serviceValidationResult['status'] !== 200) {
                return $this->createJsonResponse($response, $serviceValidationResult, 400);
            }

            $freeAppointments = new ProcessList();
            $freeAppointments = ZmsApiFacadeService::getFreeAppointments([
                'officeId' => $officeId,
                'serviceIds' => $serviceIds,
                'serviceCounts' => $serviceCounts,
                'date' => UtilityHelper::getInternalDateFromTimestamp($timestamp)
            ]);

            $processArray = json_decode(json_encode($freeAppointments), true);

            $filteredProcesses = array_filter($processArray, function ($process) use ($timestamp) {
                if (!isset($process['appointments']) || !is_array($process['appointments'])) {
                    return false;
                }
                return in_array($timestamp, array_column($process['appointments'], 'date'));
            });

            $selectedProcess = $filteredProcesses ? new Process() : null;

            if (!empty($filteredProcesses)) {
                $selectedProcessData = array_values($filteredProcesses)[0];

                $scopeData = $selectedProcessData['scope'] ?? null;
                $scope = $scopeData ? new Scope($scopeData) : null;

                $selectedProcess->withUpdatedData($selectedProcessData, new \DateTime("@$timestamp"), $scope);
            }

            $errors = ValidationService::validateGetProcessNotFound($selectedProcess);
            if (!empty($errors['errors'])) {
                return $this->createJsonResponse($response, $errors, 404);
            }

            $selectedProcess->clients = [
                [
                    'email' => 'default@example.com'
                ]
            ];

            $reservedProcess = new Process();
            $reservedProcess = ZmsApiFacadeService::reserveTimeslot($selectedProcess, $serviceIds, $serviceCounts);

            if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
                $scopeIds = [$reservedProcess->scope->id];
                $scopesData = ZmsApiFacadeService::getScopeByIds($scopeIds);

                if ($scopesData['status'] === 200 && isset($scopesData['scopes']['scopes']) && !empty($scopesData['scopes']['scopes'])) {
                    $reservedProcess->scope = MapperService::mapScope($scopesData['scopes']['scopes'][0]);
                }
            }

            $appointment = new ThinnedProcess();
            $appointment = UtilityHelper::processToThinnedProcess($reservedProcess);

            $appointment = array_merge($appointment->toArray(), ['officeId' => $officeId]);
            
            return $this->createJsonResponse($response, $appointment, 200);

        } catch (\Exception $e) {
            return $this->createJsonResponse($response, [
                'errorCode' => 'internalServerError',
                'errorMessage' => $e->getMessage()
            ], 500);
        }
    }
}