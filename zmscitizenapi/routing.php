<?php
// @codingStandardsIgnoreFile

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use BO\Zmscitizenapi\Middleware\LanguageMiddleware;

/**
 * Helper function to create routes with language prefixes
 */
function createLanguageRoutes($app, $path, $controller, $name, $method = 'get'): void
{
    // Create routes with language prefixes
    foreach (LanguageMiddleware::getSupportedLanguages() as $lang) {
        $langPath = "/{$lang}{$path}";
        if ($method === 'get') {
            $app->get($langPath, $controller)->setName("{$name}_{$lang}");
        } else {
            $app->post($langPath, $controller)->setName("{$name}_{$lang}");
        }
    }

    // Create default route without language prefix (for backward compatibility)
    if ($method === 'get') {
        $app->get($path, $controller)->setName($name);
    } else {
        $app->post($path, $controller)->setName($name);
    }
}

/**
 * @swagger
 * /services/:
 *   get:
 *     summary: Get the list of services
 *     tags:
 *       - services
 *     responses:
 *       200:
 *         description: List of services
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/collections/serviceList.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/services/',
    '\BO\Zmscitizenapi\Controllers\Service\ServicesListController',
    "ServicesListController"
);

/**
 * @swagger
 * /scopes/:
 *   get:
 *     summary: Get the list of scopes
 *     tags:
 *       - scopes
 *     responses:
 *       200:
 *         description: List of scopes
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/collections/thinnedScopeList.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/scopes/',
    '\BO\Zmscitizenapi\Controllers\Scope\ScopesListController',
    "ScopesListController"
);

/**
 * @swagger
 * /offices/:
 *   get:
 *     summary: Get the list of offices
 *     tags:
 *       - offices
 *     responses:
 *       200:
 *         description: List of offices
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/collections/officeList.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/offices/',
    '\BO\Zmscitizenapi\Controllers\Office\OfficesListController',
    "OfficesListController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/offices-and-services/',
    '\BO\Zmscitizenapi\Controllers\Office\OfficesServicesRelationsController',
    "OfficesServicesRelationsController"
);

/**
 * @swagger
 * /scope-by-id/:
 *   get:
 *     summary: Get a scope by ID
 *     tags:
 *       - scopes
 *     parameters:
 *       - name: scopeId
 *         description: Scope ID
 *         in: query
 *         required: true
 *         type: integer
 *     responses:
 *       200:
 *         description: Scope details
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/thinnedScope.json"
 *       404:
 *         description: Scope not found
 */
createLanguageRoutes(
    \App::$slim,
    '/scope-by-id/',
    '\BO\Zmscitizenapi\Controllers\Scope\ScopeByIdController',
    "ScopeByIdController"
);

/**
 * @swagger
 * /services-by-office/:
 *   get:
 *     summary: Get the services offered by a specific office
 *     tags:
 *       - services
 *     parameters:
 *       - name: officeId
 *         description: Office ID
 *         in: query
 *         required: true
 *         type: integer
 *     responses:
 *       200:
 *         description: List of services for the office
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/collections/serviceList.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/services-by-office/',
    '\BO\Zmscitizenapi\Controllers\Service\ServiceListByOfficeController',
    "ServiceListByOfficeController"
);

/**
 * @swagger
 * /offices-by-service/:
 *   get:
 *     summary: Get the offices that offer a specific service
 *     tags:
 *       - offices
 *     parameters:
 *       - name: serviceId
 *         description: Service ID
 *         in: query
 *         required: true
 *         type: integer
 *     responses:
 *       200:
 *         description: List of offices offering the service
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/collections/officeList.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/offices-by-service/',
    '\BO\Zmscitizenapi\Controllers\Office\OfficeListByServiceController',
    "OfficeListByServiceController"
);

/**
 * @swagger
 * /available-days/:
 *   get:
 *     summary: Get the list of available days for appointments
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: officeId
 *         description: Office ID
 *         in: query
 *         required: true
 *         type: integer
 *       - name: serviceId
 *         description: Service ID
 *         in: query
 *         required: true
 *         type: integer
 *     responses:
 *       200:
 *         description: List of available days
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/availableDays.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/available-days/',
    '\BO\Zmscitizenapi\Controllers\Availability\AvailableDaysListController',
    "AvailableDaysListController"
);

/**
 * @swagger
 * /available-appointments/:
 *   get:
 *     summary: Get available appointments for a specific day
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: date
 *         description: Date in format YYYY-MM-DD
 *         in: query
 *         required: true
 *         type: string
 *       - name: officeId
 *         description: Office ID
 *         in: query
 *         required: true
 *         type: integer
 *       - name: serviceId
 *         description: Service ID
 *         in: query
 *         required: true
 *         type: integer
 *     responses:
 *       200:
 *         description: List of available appointments
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/availableAppointments.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/available-appointments/',
    '\BO\Zmscitizenapi\Controllers\Availability\AvailableAppointmentsListController',
    "AvailableAppointmentsListController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/appointment/',
    '\BO\Zmscitizenapi\Controllers\Appointment\AppointmentByIdController',
    "AppointmentByIdController"
);

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
 *               $ref: "schema/citizenapi/captcha/friendlyCaptcha.json"
 */
createLanguageRoutes(
    \App::$slim,
    '/captcha-details/',
    '\BO\Zmscitizenapi\Controllers\Security\CaptchaController',
    "CaptchaController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/reserve-appointment/',
    '\BO\Zmscitizenapi\Controllers\Appointment\AppointmentReserveController',
    "AppointmentReserveController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/update-appointment/',
    '\BO\Zmscitizenapi\Controllers\Appointment\AppointmentUpdateController',
    "AppointmentUpdateController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/confirm-appointment/',
    '\BO\Zmscitizenapi\Controllers\Appointment\AppointmentConfirmController',
    "AppointmentConfirmController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/preconfirm-appointment/',
    '\BO\Zmscitizenapi\Controllers\Appointment\AppointmentPreconfirmController',
    "AppointmentPreconfirmController"
);

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
createLanguageRoutes(
    \App::$slim,
    '/cancel-appointment/',
    '\BO\Zmscitizenapi\Controllers\Appointment\AppointmentCancelController',
    "AppointmentCancelController"
);
