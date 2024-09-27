<?php

namespace BO\Zmscitizenapi;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Helper\UtilityHelper;
use BO\Zmscitizenapi\Services\CaptchaService;
use BO\Zmscitizenapi\Services\MapperService;
use BO\Zmscitizenapi\Services\ValidationService;
use BO\Zmscitizenapi\Services\ZmsApiFacadeService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentReserve extends BaseController
{


    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $request instanceof ServerRequestInterface ? $request : null;

        $body = $request->getParsedBody();

        $officeId = $body['officeId'] ?? null;
        $serviceIds = $body['serviceId'] ?? [];
        $serviceCounts = $body['serviceCount'] ?? [1];
        $captchaSolution = $body['captchaSolution'] ?? null;
        $timestamp = $body['timestamp'] ?? null;

        $errors = [];

        if (!$officeId) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Missing officeId.',
                'path' => 'officeId',
                'location' => 'body'
            ];
        } elseif (!is_numeric($officeId)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Invalid officeId format. It should be a numeric value.',
                'path' => 'officeId',
                'location' => 'body'
            ];
        }

        if (empty($serviceIds)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Missing serviceId.',
                'path' => 'serviceId',
                'location' => 'body'
            ];
        } elseif (!is_array($serviceIds) || array_filter($serviceIds, fn($id) => !is_numeric($id))) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Invalid serviceId format. It should be an array of numeric values.',
                'path' => 'serviceId',
                'location' => 'body'
            ];
        }

        if (!$timestamp) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Missing timestamp.',
                'path' => 'timestamp',
                'location' => 'body'
            ];
        } elseif (!is_numeric($timestamp) || $timestamp < 0) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Invalid timestamp format. It should be a positive numeric value.',
                'path' => 'timestamp',
                'location' => 'body'
            ];
        }

        if (!is_array($serviceCounts) || array_filter($serviceCounts, fn($count) => !is_numeric($count) || $count < 0)) {
            $errors[] = [
                'type' => 'field',
                'msg' => 'Invalid serviceCount format. It should be an array of non-negative numeric values.',
                'path' => 'serviceCount',
                'location' => 'body'
            ];
        }        

        if (!empty($errors)) {
            return $this->createJsonResponse($response, [
                'errors' => $errors,
                'status' => 400
            ], 400);
        }

        try {
            $providerScope = ZmsApiFacadeService::getScopeByOfficeId($officeId);
            $captchaRequired = Application::$CAPTCHA_ENABLED === "1" && $providerScope['captchaActivatedRequired'] === "1";

            if ($captchaRequired) {
                $captchaVerificationResult = CaptchaService::verifyCaptcha($captchaSolution);
                if (!$captchaVerificationResult['success']) {
                    return $this->createJsonResponse($response, [
                        'errorCode' => 'captchaVerificationFailed',
                        'errorMessage' => 'Captcha verification failed',
                        'lastModified' => round(microtime(true) * 1000)
                    ], 400);
                }
            }

            $serviceValidationResult = ValidationService::validateServiceLocationCombination($officeId, $serviceIds);
            if ($serviceValidationResult['status'] !== 200) {
                return $this->createJsonResponse($response, $serviceValidationResult, 400);
            }

            try {
                $internalDate = UtilityHelper::getInternalDateFromTimestamp($timestamp);
            } catch (\Exception $e) {
                return $this->createJsonResponse($response, [
                    'errorCode' => 'invalidTimestamp',
                    'errorMessage' => 'The provided timestamp is invalid.',
                    'lastModified' => round(microtime(true) * 1000)
                ], 400);
            }

            $freeAppointments = ZmsApiFacadeService::getFreeAppointments([
                'officeId' => $officeId,
                'serviceIds' => $serviceIds,
                'serviceCounts' => $serviceCounts,
                'date' => UtilityHelper::getInternalDateFromTimestamp($timestamp)
            ]);

            $selectedProcess = array_filter($freeAppointments, function ($process) use ($timestamp) {
                if (!isset($process['appointments']) || !is_array($process['appointments'])) {
                    return false;
                }
                return in_array($timestamp, array_column($process['appointments'], 'date'));
            });

            if (empty($selectedProcess)) {
                return $this->createJsonResponse($response, [
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                    'lastModified' => round(microtime(true) * 1000)
                ], 404);
            }

            $selectedProcess = array_values($selectedProcess)[0];
            $selectedProcess['clients'] = [
                [
                    'email' => 'test@muenchen.de'
                ]
            ];

            $reservedProcess = ZmsApiFacadeService::reserveTimeslot($selectedProcess, $serviceIds, $serviceCounts);

            if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
                $scopeIds = [$reservedProcess->scope->id];
                $scopesData = ZmsApiFacadeService::getScopeByIds($scopeIds);

                if ($scopesData['status'] === 200 && isset($scopesData['scopes']['scopes']) && !empty($scopesData['scopes']['scopes'])) {
                    $reservedProcess->scope = MapperService::mapScope($scopesData['scopes']['scopes'][0]);
                }
            }

            $thinnedProcessData = UtilityHelper::getThinnedProcessData($reservedProcess);
            $thinnedProcessData = array_merge($thinnedProcessData, ['officeId' => $officeId]);

            return $this->createJsonResponse($response, $thinnedProcessData, 200);

        } catch (\Exception $e) {
            error_log('Unexpected error: ' . $e->getMessage());
            return $this->createJsonResponse($response, [
                'error' => 'Unexpected error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
