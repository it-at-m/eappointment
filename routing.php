<?php
// @codingStandardsIgnoreFile
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

/* ---------------------------------------------------------------------------
 * html, basic routes
 * -------------------------------------------------------------------------*/

\App::$slim->get('/',
    '\BO\Zmsapi\Index:render')
    ->name("pagesindex");


/* ---------------------------------------------------------------------------
 * json
 * -------------------------------------------------------------------------*/
/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      get:
 *          description: Get a process
 *          parameters:
 *              -   name: id
 *                  description: process number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: authKey
 *                  description: authentication key
 *                  in: path
 *                  required: true
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      $ref: "schema/process.json"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->get('/process/:id/:authKey/',
    '\BO\Zmsapi\AppointmentGet:render')
    ->name("pagesindex");


/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      post:
 *          description: Update a process
 *          parameters:
 *              -   name: id
 *                  description: process number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: authKey
 *                  description: authentication key
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: process
 *                  description: process data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success, there might be changes on the object or added information. Use the response for further action with the process"
 *                  schema:
 *                      $ref: "schema/process.json"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  description: "process id does not exists"
 */
 \App::$slim->get('/process/:id/:authKey/',
     '\BO\Zmsapi\AppointmentPost:render')
     ->name("pagesindex");

/**
 *  @swagger
 *  "/calendar/":
 *      get:
 *          description: Get a list of available days for appointments
 *          parameters:
 *              -   name: calendar
 *                  description: data for finding available days
 *                  in: body
 *                  schema:
 *                      $ref: "schema/calendar.json"
 *          responses:
 *              200:
 *                  description: get an updated calendar objects with updated days list
 *                  schema:
 *                      $ref: "schema/calendar.json"
 *              404:
 *                  description: "Could not find any available days"
 *                  schema:
 *                      $ref: "schema/calendar.json"
 */
 \App::$slim->get('/calendar/',
     '\BO\Zmsapi\CalendarGet:render')
     ->name("pagesindex");

/**
 *  @swagger
 *  "/process/free/":
 *      get:
 *          description: Get a list of free processes for a given day
 *          parameters:
 *              -   name: calendar
 *                  description: data for finding available processes, try to restrict data to one day, if possible
 *                  in: body
 *                  schema:
 *                      $ref: "schema/calendar.json"
 *          responses:
 *              200:
 *                  description: get an updated calendar objects with updated days list
 *                  schema:
 *                      type: array
 *                      items:
 *                          $ref: "schema/process.json"
 *              404:
 *                  description: "Could not find any available processes, returns empty list"
 *                  schema:
 *                      type: array
 *                      items:
 *                          $ref: "schema/process.json"
 */
 \App::$slim->get('/calendar/',
     '\BO\Zmsapi\CalendarGet:render')
     ->name("pagesindex");


//\App::$slim->get('/dienstleistung/:service_id',
//    '\BO\Zmsapi\ServiceDetail:render')
//    ->conditions([
//        'service_id' => '\d{3,10}',
//        ])
//    ->name("servicedetail");

/* ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------*/

\App::$slim->get('/healthcheck/',
    '\BO\Zmsapi\Healthcheck:render')
    ->name("healthcheck");

\App::$slim->notfound(function () {
    \BO\Slim\Render::html('404.twig');
});

\App::$slim->error(function (\Exception $exception) {
    \BO\Slim\Render::lastModified(time(), '0');
    \BO\Slim\Render::html('failed.twig', array(
        "failed" => $exception->getMessage(),
        "error" => $exception,
    ));
    \App::$slim->stop();
});
