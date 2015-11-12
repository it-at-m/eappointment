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
*                      type: object
*                      properties:
*                          meta:
*                              $ref: "schema/metaresult.json"
*                          data:
*                              $ref: "schema/calendar.json"
*              404:
*                  description: "Could not find any available days"
*                  schema:
*                      $ref: "schema/calendar.json"
*/
\App::$slim->get('/calendar/',
    '\BO\Zmsapi\CalendarGet:render')
    ->name("CalendarGet");

/**
 *  @swagger
 *  "/mails/":
 *      get:
 *          description: get a list of mails in the send queue
 *          responses:
 *              200:
 *                  description: returns a list, might be empty
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/mail.json"
 */
\App::$slim->get('/mails/',
    '\BO\Zmsapi\MailGet:render')
    ->name("MailGet");



/**
 *  @swagger
 *  "/mails/":
 *      post:
 *          description: Add a mail to the send queue
 *          parameters:
 *              -   name: notification
 *                  description: mail data to send
 *                  in: body
 *                  schema:
 *                      $ref: "schema/mail.json"
 *          responses:
 *              200:
 *                  description: mail accepted
 *              400:
 *                  description: "Missing required properties in the notification"
 */
\App::$slim->post('/mails/',
    '\BO\Zmsapi\MailAdd:render')
    ->name("MailAdd");

/**
 *  @swagger
 *  "/mails/{id}":
 *      delete:
 *          description: delete a mail in the send queue
 *          parameters:
 *              -   name: id
 *                  description: mail number
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: succesfully deleted
 *              404:
 *                  description: "could not find mail or mail already sent"
 */
\App::$slim->delete('/mails/{id}',
    '\BO\Zmsapi\MailDelete:render')
    ->conditions([
        'id' => '\d{4,11}',
     ])
    ->name("MailDelete");


/**
 *  @swagger
 *  "/notifications/":
 *      get:
 *          description: get a list of notifications in the send queue
 *          responses:
 *              200:
 *                  description: returns a list, might be empty
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/notification.json"
 */
\App::$slim->get('/notifications/',
    '\BO\Zmsapi\NotificationsGet:render')
    ->name("NotificationsGet");



/**
 *  @swagger
 *  "/notifications/":
 *      post:
 *          description: Add a notification to the send queue
 *          parameters:
 *              -   name: notification
 *                  description: notification data to send
 *                  in: body
 *                  schema:
 *                      $ref: "schema/notification.json"
 *          responses:
 *              200:
 *                  description: notification accepted
 *              400:
 *                  description: "Missing required properties in the notification"
 */
\App::$slim->post('/notifications/',
    '\BO\Zmsapi\NotificationsAdd:render')
    ->name("NotificationsAdd");

/**
 *  @swagger
 *  "/notifications/{id}":
 *      delete:
 *          description: delete a notification in the send queue
 *          parameters:
 *              -   name: id
 *                  description: notification number
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: succesfully deleted
 *              404:
 *                  description: "could not find notification or notification already sent"
 */
\App::$slim->delete('/notifications/{id}',
    '\BO\Zmsapi\NotificationDelete:render')
    ->conditions([
        'id' => '\d{4,11}',
     ])
    ->name("NotificationDelete");


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
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/process.json"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->get('/process/:id/:authKey/',
    '\BO\Zmsapi\ProcessGet:render')
    ->conditions([
        'id' => '\d{4,11}',
     ])
    ->name("ProcessGet");


/**
 *  @swagger
 *  "/process/{id}/{authKey}/ics/":
 *      get:
 *          description: Get an ICS-File for a process
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
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: object
 *                              properties:
 *                                  content:
 *                                      type: string
 *                                      description: "base64 encoded ICS file"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->get('/process/:id/:authKey/ics/',
    '\BO\Zmsapi\ProcessIcs:render')
    ->conditions([
        'id' => '\d{4,11}',
     ])
    ->name("ProcessIcs");


/**
 *  @swagger
 *  "/process/{id}/":
 *      post:
 *          description: Update a process but does not send any mails or notifications on status changes
 *          parameters:
 *              -   name: id
 *                  description: process number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: process
 *                  description: process data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success, there might be changes on the object or added information. Use the response for further action with the process"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/process.json"
 *              400:
 *                  description: "Invalid input"
 *              401:
 *                  description: "authkey does not match"
 *              403:
 *                  description: "forbidden, this function does not allow status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post('/process/:id/:authKey/',
    '\BO\Zmsapi\ProcessUpdate:render')
    ->conditions([
        'id' => '\d{4,11}',
    ])
    ->name("ProcessUpdate");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      delete:
 *          description: Deletes a process but does not send any mails or notifications
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
 *                  description: "success, there might be changes on the object or added information. Use the response for further action with the process"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/process.json"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->delete('/process/:id/:authKey/',
    '\BO\Zmsapi\ProcessDelete:render')
    ->conditions([
        'id' => '\d{4,11}',
    ])
    ->name("ProcessDelete");

/**
 *  @swagger
 *  "/process/status/free/":
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
 *                  description: get a list of available processes
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 *              404:
 *                  description: "Could not find any available processes, returns empty list"
 *                  schema:
 *                      type: array
 *                      items:
 *                          $ref: "schema/process.json"
 */
\App::$slim->get('/process/status/free/',
    '\BO\Zmsapi\ProcessFree:render')
    ->name("ProcessFree");

/**
 *  @swagger
 *  "/process/status/reserved/":
 *      get:
 *          description: Get a list of reserved processes
 *          responses:
 *              200:
 *                  description: get a list of processes
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 *              404:
 *                  description: "Could not find any processes, returns empty list"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 */
\App::$slim->get('/process/status/reserved/',
    '\BO\Zmsapi\ProcessReservedList:render')
    ->name("ProcessReservedList");

/**
 *  @swagger
 *  "/process/status/reserved/":
 *      post:
 *          description: Try to reserve the appointments in a process
 *          parameters:
 *              -   name: process
 *                  description: process data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: get a list of processes
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 *              400:
 *                  description: "Invalid input"
 *              404:
 *                  description: "Could not find any processes, returns empty list"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 */
\App::$slim->get('/process/status/reserved/',
    '\BO\Zmsapi\ProcessReserve:render')
    ->name("ProcessReserve");

/**
 *  @swagger
 *  "/process/status/confirmed/":
 *      post:
 *          description: Try to confirm a process, changes status from reservered to confirmed
 *          parameters:
 *              -   name: process
 *                  description: process data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: process is confirmed, notifications and mails sent according to preferences
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 *              302:
 *                  description: "Redirects to /processes/status/reserved/ since the given process does not exists in the list (any longer)"
 *              400:
 *                  description: "Invalid input"
 */
\App::$slim->get('/process/status/confirmed/',
    '\BO\Zmsapi\ProcessConfirm:render')
    ->name("ProcessConfirm");

/**
 *  @swagger
 *  "/scope/":
 *      get:
 *          description: Get a list of scopes
 *          responses:
 *              200:
 *                  description: "returns a list"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/scope.json"
 *              404:
 *                  description: "no scopes defined yet"
 */
\App::$slim->get('/scope/:id/',
    '\BO\Zmsapi\ScopeList:render')
    ->conditions([
        'id' => '\d{1,11}',
     ])
    ->name("ScopeList");

/**
 *  @swagger
 *  "/scope/{id}":
 *      get:
 *          description: Get a scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/scope.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get('/scope/:id/',
    '\BO\Zmsapi\ScopeGet:render')
    ->conditions([
        'id' => '\d{1,11}',
     ])
    ->name("ScopeGet");

/**
 *  @swagger
 *  "/scope/{id}":
 *      post:
 *          description: Update a scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: scope
 *                  description: scope content
 *                  in: body
 *                  required: true
 *                  schema:
 *                      $ref: "schema/scope.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/scope.json"
 *              400:
 *                  description: "Invalid input"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post('/scope/:id/',
    '\BO\Zmsapi\ScopeUpdate:render')
    ->conditions([
        'id' => '\d{1,11}',
     ])
    ->name("ScopeUpdate");

/**
 *  @swagger
 *  "/scope/{id}":
 *      delete:
 *          description: Delete a scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->delete('/scope/:id/',
    '\BO\Zmsapi\ScopeDelete:render')
    ->conditions([
        'id' => '\d{1,11}',
     ])
    ->name("ScopeDelete");

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
