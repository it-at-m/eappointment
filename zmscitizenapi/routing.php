<?php
// @codingStandardsIgnoreFile

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

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
 *               $ref: "schema/citizenapi/services.json"
 */
\App::$slim->get(
    '/services/',
    '\BO\Zmscitizenapi\ServicesList'
)
    ->setName("ServicesList");

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
 *               $ref: "schema/citizenapi/scopes.json"
 */
\App::$slim->get(
    '/scopes/',
    '\BO\Zmscitizenapi\ScopesList'
)
    ->setName("ScopesList");

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
 *               $ref: "schema/citizenapi/offices.json"
 */
\App::$slim->get(
    '/offices/',
    '\BO\Zmscitizenapi\OfficesList'
)
    ->setName("OfficesList");

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
 *               $ref: "schema/citizenapi/offices-and-services.json"
 */
\App::$slim->get(
    '/offices-and-services/',
    '\BO\Zmscitizenapi\OfficesServicesRelations'
)
    ->setName("OfficesServicesRelations");

/**
 * @swagger
 * /scope-by-id/:
 *   get:
 *     summary: Get a scope by ID
 *     tags:
 *       - scopes
 *     parameters:
 *       - name: id
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
 *               $ref: "schema/citizenapi/scope.json"
 *       404:
 *         description: Scope not found
 */
\App::$slim->get(
    '/scope-by-id/',
    '\BO\Zmscitizenapi\ScopeByIdGet'
)
    ->setName("ScopeByIdGet");

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
 *               $ref: "schema/citizenapi/services-by-office.json"
 */
\App::$slim->get(
    '/services-by-office/',
    '\BO\Zmscitizenapi\ServicesByOfficeList'
)
    ->setName("ServicesByOfficeList");

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
 *               $ref: "schema/citizenapi/offices-by-service.json"
 */
\App::$slim->get(
    '/offices-by-service/',
    '\BO\Zmscitizenapi\OfficesByServiceList'
)
    ->setName("OfficesByServiceList");

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
 *               $ref: "schema/citizenapi/available-days.json"
 */
\App::$slim->get(
    '/available-days/',
    '\BO\Zmscitizenapi\AvailableDaysList'
)
    ->setName("AvailableDaysList");

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
 *               $ref: "schema/citizenapi/available-appointments.json"
 */
\App::$slim->get(
    '/available-appointments/',
    '\BO\Zmscitizenapi\AvailableAppointmentsList'
)
    ->setName("AvailableAppointmentsList");

/**
 * @swagger
 * /appointment/:
 *   get:
 *     summary: Get an appointment by process ID
 *     tags:
 *       - appointments
 *     parameters:
 *       - name: processId
 *         description: Process ID
 *         in: query
 *         required: true
 *         type: integer
 *       - name: authKey
 *         description: Authentication key
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
 *               $ref: "schema/citizenapi/appointment.json"
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
\App::$slim->get(
    '/appointment/',
    '\BO\Zmscitizenapi\AppointmentGet'
)
    ->setName("AppointmentGet");

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
 *               $ref: "schema/citizenapi/captcha-details.json"
 */
\App::$slim->get(
    '/captcha-details/',
    '\BO\Zmscitizenapi\CaptchaGet'
)
    ->setName("CaptchaGet");

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
 *           $ref: "schema/citizenapi/appointment-reserve.json"
 *     responses:
 *       200:
 *         description: Reservation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/appointment.json"
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
\App::$slim->post(
    '/reserve-appointment/',
    '\BO\Zmscitizenapi\AppointmentReserve'
)
    ->setName("AppointmentReserve");

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
 *           $ref: "schema/citizenapi/appointment-update.json"
 *     responses:
 *       200:
 *         description: Update successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/appointment.json"
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
\App::$slim->post(
    '/update-appointment/',
    '\BO\Zmscitizenapi\AppointmentUpdate'
)
    ->setName("AppointmentUpdate");

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
 *           $ref: "schema/citizenapi/appointment-confirm.json"
 *     responses:
 *       200:
 *         description: Confirmation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/appointment.json"
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
\App::$slim->post(
    '/confirm-appointment/',
    '\BO\Zmscitizenapi\AppointmentConfirm'
)
    ->setName("AppointmentConfirm");

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
 *           $ref: "schema/citizenapi/appointment-preconfirm.json"
 *     responses:
 *       200:
 *         description: Preconfirmation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/appointment.json"
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
\App::$slim->post(
    '/preconfirm-appointment/',
    '\BO\Zmscitizenapi\AppointmentPreconfirm'
)
    ->setName("AppointmentPreconfirm");

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
 *           $ref: "schema/citizenapi/appointment-cancel.json"
 *     responses:
 *       200:
 *         description: Cancellation successful
 *         schema:
 *           type: object
 *           properties:
 *             meta:
 *               $ref: "schema/metaresult.json"
 *             data:
 *               $ref: "schema/citizenapi/appointment.json"
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
\App::$slim->post(
    '/cancel-appointment/',
    '\BO\Zmscitizenapi\AppointmentCancel'
)
    ->setName("AppointmentCancel");
