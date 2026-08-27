<?php
// @codingStandardsIgnoreFile

/**
 * @swagger
 * /offices-and-services/:
 *   get:
 *     summary: Get the relations between offices and services
 *     tags:
 *       - offices-services
 *     responses:
 *       200:
 *         description: List of office-service relations
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/collections/officeServiceAndRelationList.json"
 */
\App::$slim->get('/offices-and-services/', \BO\Zmscitizenapi\Controllers\Office\OfficesServicesRelationsController::class)->setName("OfficesServicesRelationsController");

/**
 * @swagger
 * /available-calendar/:
 *   get:
 *     summary: Get bookable days with appointment slots grouped by office
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: startDate
 *         description: Start date in format YYYY-MM-DD
 *         in: query
 *         required: true
 *         type: string
 *       - name: endDate
 *         description: End date in format YYYY-MM-DD
 *         in: query
 *         required: true
 *         type: string
 *       - name: slotsStartDate
 *         description: Start of appointment slots window (YYYY-MM-DD). Defaults to startDate.
 *         in: query
 *         required: false
 *         type: string
 *       - name: slotsEndDate
 *         description: End of appointment slots window (YYYY-MM-DD). Defaults to endDate.
 *         in: query
 *         required: false
 *         type: string
 *       - name: officeIds
 *         description: Comma-separated office IDs (plural CSV list query param)
 *         in: query
 *         required: true
 *         type: string
 *       - name: serviceIds
 *         description: Comma-separated service IDs (plural CSV list query param)
 *         in: query
 *         required: true
 *         type: string
 *       - name: serviceCounts
 *         description: Comma-separated service counts matching serviceIds order (plural CSV list query param)
 *         in: query
 *         required: true
 *         type: string
 *     responses:
 *       200:
 *         description: Combined list of available days and appointment slots
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/availableCalendar.json"
 */
\App::$slim->get('/available-calendar/', \BO\Zmscitizenapi\Controllers\Availability\AvailableCalendarController::class)->setName("AvailableCalendarController");

/**
 * @swagger
 * /appointment/:
 *   get:
 *     summary: Get an appointment by process ID
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: processId
 *         description: The unique identifier for the process. Must be an integer starting with 10 or 11, e.g., 100348.
 *         in: query
 *         required: true
 *         type: integer
 *       - name: authKey
 *         description: The authentication key consisting of 4 to 5 alphanumeric characters, e.g., 42a3.
 *         in: query
 *         required: true
 *         type: string
 *     responses:
 *       200:
 *         description: Appointment details
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedProcess.json"
 *       400:
 *         description: Invalid input
 *         schema:
 *           type: object
 *           properties:
 *             errors:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   type:
 *                     type: string
 *                   msg:
 *                     type: string
 *                   path:
 *                     type: string
 *                   location:
 *                     type: string
 *       404:
 *         description: Appointment not found
 */
\App::$slim->get('/appointment/', \BO\Zmscitizenapi\Controllers\Appointment\AppointmentByIdController::class)->setName("AppointmentByIdController");

/**
 * @swagger
 * /captcha-details/:
 *   get:
 *     summary: Get CAPTCHA details
 *     tags:
 *       - captcha
 *     responses:
 *       200:
 *         description: CAPTCHA details
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/captcha/altchaCaptcha.json"
 */
\App::$slim->get('/captcha-details/', \BO\Zmscitizenapi\Controllers\Captcha\CaptchaController::class)->setName("CaptchaController");

/**
 * @swagger
 * /captcha-challenge/:
 *   get:
 *     summary: Create a new CAPTCHA challenge
 *     tags:
 *       - captcha
 *     responses:
 *       200:
 *         description: CAPTCHA challenge created
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/captcha/createChallengeResponse.json"
 */
\App::$slim->get('/captcha-challenge/', \BO\Zmscitizenapi\Controllers\Captcha\CaptchaChallengeController::class)->setName("CaptchaChallengeController");

/**
 * @swagger
 * /captcha-verify/:
 *   post:
 *     summary: Verify CAPTCHA challenge response
 *     tags:
 *       - captcha
 *     parameters:
 *       - in: body
 *         name: captchaResponse
 *         description: CAPTCHA response to verify
 *         required: true
 *         schema:
 *           $ref: "schema/citizenapi/captcha/verifySolutionRequest.json"
 *     responses:
 *       200:
 *         description: CAPTCHA verification result
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/captcha/verifySolutionResponse.json"
 */
\App::$slim->post('/captcha-verify/', \BO\Zmscitizenapi\Controllers\Captcha\CaptchaVerifyController::class)->setName("CaptchaVerifyController");

/**
 * @swagger
 * /reserve-appointment/:
 *   post:
 *     summary: Reserve an appointment
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: appointment
 *         description: Appointment reservation data
 *         in: body
 *         required: true
 *         schema:
 *           $ref: "schema/citizenapi/appointmentReserve.json"
 *     responses:
 *       200:
 *         description: Reservation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedProcess.json"
 *       400:
 *         description: Invalid input
 *         schema:
 *           type: object
 *           properties:
 *             errors:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   type:
 *                     type: string
 *                   msg:
 *                     type: string
 *                   path:
 *                     type: string
 *                   location:
 *                     type: string
 *       404:
 *         description: Appointment not found
 */
\App::$slim->post('/reserve-appointment/', \BO\Zmscitizenapi\Controllers\Appointment\AppointmentReserveController::class)->setName("AppointmentReserveController");

/**
 * @swagger
 * /update-appointment/:
 *   post:
 *     summary: Update an appointment
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: appointment
 *         description: Appointment update data
 *         in: body
 *         required: true
 *         schema:
 *           $ref: "schema/citizenapi/appointmentUpdate.json"
 *     responses:
 *       200:
 *         description: Update successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedProcess.json"
 *       400:
 *         description: Invalid input
 *         schema:
 *           type: object
 *           properties:
 *             errors:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   type:
 *                     type: string
 *                   msg:
 *                     type: string
 *                   path:
 *                     type: string
 *                   location:
 *                     type: string
 *       404:
 *         description: Appointment not found
 */
\App::$slim->post('/update-appointment/', \BO\Zmscitizenapi\Controllers\Appointment\AppointmentUpdateController::class)->setName("AppointmentUpdateController");

/**
 * @swagger
 * /confirm-appointment/:
 *   post:
 *     summary: Confirm an appointment
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: appointment
 *         description: Appointment confirmation data
 *         in: body
 *         required: true
 *         schema:
 *           $ref: "schema/citizenapi/appointmentConfirm.json"
 *     responses:
 *       200:
 *         description: Confirmation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedProcess.json"
 *       400:
 *         description: Invalid input
 *         schema:
 *           type: object
 *           properties:
 *             errors:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   type:
 *                     type: string
 *                   msg:
 *                     type: string
 *                   path:
 *                     type: string
 *                   location:
 *                     type: string
 *       404:
 *         description: Appointment not found
 */
\App::$slim->post('/confirm-appointment/', \BO\Zmscitizenapi\Controllers\Appointment\AppointmentConfirmController::class)->setName("AppointmentConfirmController");

/**
 * @swagger
 * /preconfirm-appointment/:
 *   post:
 *     summary: Preconfirm an appointment
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: appointment
 *         description: Appointment preconfirmation data
 *         in: body
 *         required: true
 *         schema:
 *           $ref: "schema/citizenapi/appointmentPreconfirm.json"
 *     responses:
 *       200:
 *         description: Preconfirmation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedProcess.json"
 *       400:
 *         description: Invalid input
 *         schema:
 *           type: object
 *           properties:
 *             errors:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   type:
 *                     type: string
 *                   msg:
 *                     type: string
 *                   path:
 *                     type: string
 *                   location:
 *                     type: string
 *       404:
 *         description: Appointment not found
 */
\App::$slim->post('/preconfirm-appointment/', \BO\Zmscitizenapi\Controllers\Appointment\AppointmentPreconfirmController::class)->setName("AppointmentPreconfirmController");

/**
 * @swagger
 * /cancel-appointment/:
 *   post:
 *     summary: Cancel an appointment
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: appointment
 *         description: Appointment cancellation data
 *         in: body
 *         required: true
 *         schema:
 *           $ref: "schema/citizenapi/appointmentCancel.json"
 *     responses:
 *       200:
 *         description: Cancellation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedProcess.json"
 *       400:
 *         description: Invalid input
 *         schema:
 *           type: object
 *           properties:
 *             errors:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   type:
 *                     type: string
 *                   msg:
 *                     type: string
 *                   path:
 *                     type: string
 *                   location:
 *                     type: string
 *       404:
 *         description: Appointment not found
 */
\App::$slim->post('/cancel-appointment/', \BO\Zmscitizenapi\Controllers\Appointment\AppointmentCancelController::class)->setName("AppointmentCancelController");

/**
 * @swagger
 * /my-appointments/:
 *   get:
 *     summary: Get all appointments for the currently logged-in user
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: filterId
 *         description: "Get a certain process for a given user"
 *         in: query
 *         type: integer
 *     responses:
 *       200:
 *         description: List of appointments
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               type: array
 *               items:
 *                 $ref: "schema/citizenapi/thinnedProcess.json"
 *       401:
 *         description: Unauthorized (if no user header is present)
 */
\App::$slim->get('/my-appointments/', \BO\Zmscitizenapi\Controllers\Appointment\MyAppointmentsController::class)->setName("MyAppointmentsController");

/**
 * @swagger
 * /source-cache/warmup/:
 *   post:
 *     summary: Refresh the offices-and-services source cache in place
 *     tags:
 *       - cache
 *     parameters:
 *       - name: X-Source-Cache-Warmup-Token
 *         in: header
 *         required: true
 *         type: string
 *     responses:
 *       200:
 *         description: Fresh source data fetched and offices-and-services cache overwritten
 *       403:
 *         description: Missing or invalid warmup token
 *       404:
 *         description: Warmup disabled (SOURCE_CACHE_WARMUP_TOKEN unset)
 */
\App::$slim->post(
    '/source-cache/warmup/',
    '\BO\Zmscitizenapi\Controllers\System\SourceCacheWarmupController'
)->setName("SourceCacheWarmupController");

// Catch-all route for 404 errors
\App::$slim->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], '/{routes:.+}', function ($request, $response) {
    $error = \BO\Zmscitizenapi\Utils\ErrorMessages::get('notFound');
    $response = $response->withStatus($error['statusCode']);
    $response->getBody()->write(json_encode([
        'errors' => [
            $error
        ]
    ]));
    return $response->withHeader('Content-Type', 'application/json');
})->setName('notFound');
