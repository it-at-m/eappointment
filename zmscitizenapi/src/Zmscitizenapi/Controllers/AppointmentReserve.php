<?php

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\CaptchaService;
use BO\Zmscitizenapi\Services\ScopesService;
use BO\Zmscitizenapi\Services\OfficesServicesRelationsService;
use BO\Zmscitizenapi\Services\AvailableAppointmentsService;
use BO\Zmscitizenapi\Services\AppointmentService;
use BO\Zmscitizenapi\Services\ProcessService;
use BO\Zmscitizenapi\Helper\UtilityHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppointmentReserve extends BaseController
{
    protected $captchaService;
    protected $scopesService;
    protected $officesServicesRelationsService;
    protected $availableAppointmentsService;
    protected $appointmentService;
    protected $utilityHelper;
    protected $processService;

    public function __construct()
    {
        $this->captchaService = new CaptchaService();
        $this->scopesService = new ScopesService();
        $this->officesServicesRelationsService = new OfficesServicesRelationsService();
        $this->availableAppointmentsService = new AvailableAppointmentsService();
        $this->processService = new ProcessService(\App::$http);
        $this->appointmentService = new AppointmentService($this->processService);
        $this->utilityHelper = new UtilityHelper();
    }

    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $request instanceof ServerRequestInterface ? $request : null;

        if (!$request) {
            return $this->createJsonResponse($response, [
                'error' => 'Invalid request object',
                'status' => 400
            ], 400);
        }

        $body = $request->getParsedBody();
        if (is_null($body)) {
            return $this->createJsonResponse($response, [
                'error' => 'Invalid or missing request body',
                'status' => 400
            ], 400);
        }

        $officeId = $body['officeId'] ?? null;
        $serviceIds = $body['serviceId'] ?? [];
        $serviceCounts = $body['serviceCount'] ?? [1];
        $captchaSolution = $body['captchaSolution'] ?? null;
        $timestamp = $body['timestamp'] ?? null;

        if (!$officeId || empty($serviceIds) || !$timestamp) {
            return $this->createJsonResponse($response, [
                'error' => 'Missing required fields',
                'status' => 400
            ], 400);
        }

        try {
            $providerScope = $this->scopesService->getScopeByOfficeId($officeId);
            $captchaRequired = Application::$CAPTCHA_ENABLED === "1" && $providerScope['captchaActivatedRequired'] === "1";

            if ($captchaRequired) {
                $captchaVerificationResult = $this->captchaService->verifyCaptcha($captchaSolution);
                if (!$captchaVerificationResult['success']) {
                    return $this->createJsonResponse($response, [
                        'errorCode' => 'captchaVerificationFailed',
                        'errorMessage' => 'Captcha verification failed',
                        'lastModified' => round(microtime(true) * 1000)
                    ], 400);
                }
            }

            $serviceValidationResult = $this->officesServicesRelationsService->validateServiceLocationCombination($officeId, $serviceIds);
            if ($serviceValidationResult['status'] !== 200) {
                return $this->createJsonResponse($response, $serviceValidationResult, 400);
            }

            $freeAppointments = $this->availableAppointmentsService->getFreeAppointments([
                'officeId' => $officeId,
                'serviceIds' => $serviceIds,
                'serviceCounts' => $serviceCounts,
                'date' => $this->utilityHelper->getInternalDateFromTimestamp($timestamp)
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

            $reservedProcess = $this->processService->reserveTimeslot($selectedProcess, $serviceIds, $serviceCounts);

            if ($reservedProcess && $reservedProcess->scope && $reservedProcess->scope->id) {
                $scopeIds = [$reservedProcess->scope->id];
                $scopesData = $this->scopesService->getScopeByIds($scopeIds);
            
                if ($scopesData['status'] === 200 && isset($scopesData['scopes']['scopes']) && !empty($scopesData['scopes']['scopes'])) {
                    $reservedProcess->scope = $this->scopesService->mapScope($scopesData['scopes']['scopes'][0]);
                }
            }            
            
            $thinnedProcessData = $this->appointmentService->getThinnedProcessData($reservedProcess);
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
