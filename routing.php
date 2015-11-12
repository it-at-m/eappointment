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
  \App::$slim->get('/appointment/:id/',
      '\BO\Zmsapi\AppointmentGet:render')
      ->name("pagesindex");


/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      post:
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
 *              -   name: process
 *                  description: process data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
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
 \App::$slim->get('/appointment/:id/',
     '\BO\Zmsapi\AppointmentPost:render')
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
