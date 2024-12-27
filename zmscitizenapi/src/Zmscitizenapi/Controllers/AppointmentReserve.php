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
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\ProcessList;
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
            $captchaRequired = Application::$CAPTCHA_ENABLED === true && isset($providerScope->captchaActivatedRequired) && $providerScope->captchaActivatedRequired === "1";

            if ($captchaRequired) {
                try {
                    $captcha = new FriendlyCaptcha();
                    $captchaVerificationResult = $captcha->verifyCaptcha($captchaSolution);
                    if (!$captchaVerificationResult) {
                        return $this->createJsonResponse($response, ErrorMessages::get('captchaVerificationFailed'), 400);
                    }
                } catch (\Exception $e) {
                    return $this->createJsonResponse($response, ErrorMessages::get('captchaVerificationError'), 500);
                }
            }

            $errors = ValidationService::validateServiceLocationCombination($officeId, $serviceIds);
            if (!empty($errors['errors'])) {
                return $this->createJsonResponse($response, $errors, 400);
            }

            $freeAppointments = new ProcessList();
            $freeAppointments = ZmsApiFacadeService::getFreeAppointments(
                $officeId,
                $serviceIds,
                $serviceCounts,
                DateTimeFormatHelper::getInternalDateFromTimestamp($timestamp)
            );

            $processArray = json_decode(json_encode($freeAppointments), true); //Todo cleanup

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
                    'email' => 'test@muenchen.de'
                ]
            ];

            try {
                $reservedProcess = ZmsApiFacadeService::reserveTimeslot($selectedProcess, $serviceIds, $serviceCounts);
            } catch (\Exception $e) {
                throw $e;
            }

            if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {

                $scopeId = $reservedProcess->scope->id;
                $scope = ZmsApiFacadeService::getScopeById((int) $scopeId);

                if (!isset($scope['errors']) && isset($scope) && !empty($scope)) {
                    $reservedProcess->scope = $scope;
                }
            }

            $thinnedProcess = array_merge($reservedProcess->toArray(), ['officeId' => $officeId]);

            return $this->createJsonResponse($response, $thinnedProcess, 200);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}