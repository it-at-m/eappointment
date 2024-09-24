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

    // This method signature matches the BaseController's method signature
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // Cast RequestInterface to ServerRequestInterface
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
            // Step 1: Validate the scope for the office and check if captcha is required
            $providerScope = $this->scopesService->getScopeByOfficeId($officeId); // we need to mock the scope
            $captchaRequired = Application::$CAPTCHA_ENABLED === "1" && $providerScope['captchaActivatedRequired'] === "1";

            // Step 2: If captcha is required, verify it
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

            // Step 3: Validate the service-location combination
            $serviceValidationResult = $this->officesServicesRelationsService->validateServiceLocationCombination($officeId, $serviceIds);
            if ($serviceValidationResult['status'] !== 200) {
                return $this->createJsonResponse($response, $serviceValidationResult, 400);
            }


            // Step 4: Get available timeslots using the AvailableAppointmentsService
            $freeAppointments = $this->availableAppointmentsService->getFreeAppointments([
                'officeId' => $officeId,
                'serviceIds' => $serviceIds,
                'serviceCounts' => $serviceCounts,
                'date' => $this->utilityHelper->getInternalDateFromTimestamp($timestamp)
            ]);

            // **Step 5: Find the matching time slot based on the requested timestamp**
            $selectedProcess = array_filter($freeAppointments, function ($process) use ($timestamp) {
                // Ensure 'appointments' is set and is an array before accessing it
                if (!isset($process['appointments']) || !is_array($process['appointments'])) {
                    return false;
                }
                // Find the appointment slot with the exact matching timestamp
                return in_array($timestamp, array_column($process['appointments'], 'date'));
            });

            if (empty($selectedProcess)) {
                return $this->createJsonResponse($response, [
                    'errorCode' => 'appointmentNotAvailable',
                    'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
                    'lastModified' => round(microtime(true) * 1000)
                ], 404);
            }

            // Step 6: Prepare the process for reservation
            $selectedProcess = array_values($selectedProcess)[0];
            $selectedProcess['clients'] = [
                [
                    'email' => 'test@muenchen.de'
                ]
            ];

            // Step 7: Reserve the appointment using the ProcessService
            $reservedProcess = $this->processService->reserveTimeslot($selectedProcess, $serviceIds, $serviceCounts);

            // Step 8: Use the AppointmentService's getThinnedProcessData method
            $thinnedProcessData = $this->appointmentService->getThinnedProcessData($reservedProcess);

            // Step 9: Return the thinned process data
            return $this->createJsonResponse($response, [
                'reservedProcess' => $thinnedProcessData,
                'officeId' => $officeId
            ], 200);

        } catch (\Exception $e) {
            error_log('Unexpected error: ' . $e->getMessage());
            return $this->createJsonResponse($response, [
                'error' => 'Unexpected error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
