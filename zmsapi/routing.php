<?php
// @codingStandardsIgnoreFile
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/* ---------------------------------------------------------------------------
 * html, basic routes
 * -------------------------------------------------------------------------*/

\App::$slim->get(
    '/',
    '\BO\Zmsapi\Index'
)
    ->setName("index");


/* ---------------------------------------------------------------------------
 * json
 * -------------------------------------------------------------------------*/

/**
 *  @swagger
 *  "/apikey/{key}/":
 *      get:
 *          summary: Get quotas if key is active
 *          tags:
 *              - apikey
 *          parameters:
 *              -   name: key
 *                  description: key for public api access
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
 *                              $ref: "schema/apikey.json"
 *              404:
 *                  description: "access failed"
 */
\App::$slim->get(
    '/apikey/{key}/',
    '\BO\Zmsapi\ApikeyGet'
)
    ->setName("ApikeyGet");

/**
 *  @swagger
 *  "/apikey/":
 *      post:
 *          summary: Activate or update apikey
 *          tags:
 *             - apikey
 *          parameters:
 *              -   name: apikey
 *                  description: apikey data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/apikey.json"
 *              -   name: clientkey
 *                  description: clientkey to identify api client
 *                  type: string
 *                  in: query
 *                  required: false
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/apikey.json"
 *              404:
 *                  description: "access failed"
 */
\App::$slim->post(
    '/apikey/',
    \BO\Zmsapi\ApikeyUpdate::class
)
    ->setName("ApikeyUpdate");

/**
 *  @swagger
 *  "/apikey/{key}/":
 *      delete:
 *          summary: Deletes an apikey
 *          tags:
 *              - apikey
 *          parameters:
 *              -   name: key
 *                  description: key for public api access
 *                  in: path
 *                  required: true
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success, returns deleted object or empty if object did not exists"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/apikey.json"
 */
\App::$slim->delete(
    '/apikey/{key}/',
    '\BO\Zmsapi\ApikeyDelete'
)
    ->setName("ApikeyDelete");

/**
 *  @swagger
 *  "/availability/{id}/":
 *      get:
 *          summary: Get an availability by id
 *          tags:
 *              - availability
 *          parameters:
 *              -   name: id
 *                  description: availability number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/availability.json"
 *              404:
 *                  description: "availability id does not exists"
 */
\App::$slim->get(
    '/availability/{id:\d{1,11}}/',
    '\BO\Zmsapi\AvailabilityGet'
)
    ->setName("AvailabilityGet");

/**
 *  @swagger
 *  "/availability/":
 *      post:
 *          summary: Create or update availabilities. If an entity has an id, an update is performed
 *          tags:
 *              - availability
 *          parameters:
 *              -   name: availability
 *                  description: availabilityList data to update
 *                  in: body
 *                  schema:
 *                      type: array
 *                      items:
 *                          $ref: "schema/availability.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/availability.json"
 *              404:
 *                  description: "availability id does not exists"
 */
\App::$slim->post(
    '/availability/',
    '\BO\Zmsapi\AvailabilityAdd'
)
    ->setName("AvailabilityAdd");

/**
 *  @swagger
 *  "/availability/{id}/":
 *      post:
 *          summary: Update an availability
 *          tags:
 *              - availability
 *          parameters:
 *              -   name: id
 *                  description: availability number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: availability
 *                  description: availability data to update
 *                  in: body
 *                  schema:
 *                      $ref: "schema/availability.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/availability.json"
 *              404:
 *                  description: "availability id does not exists"
 */
\App::$slim->post(
    '/availability/{id:\d{1,11}}/',
    '\BO\Zmsapi\AvailabilityUpdate'
)
    ->setName("AvailabilityUpdate");

/**
 *  @swagger
 *  "/availability/slots/update/":
 *      post:
 *          summary: Update slots by availabilitylist
 *          tags:
 *              - availability
 *          parameters:
 *              -   name: availability
 *                  description: availabilityList data to update slots
 *                  in: body
 *                  schema:
 *                      type: array
 *                      items:
 *                          $ref: "schema/availability.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/availability.json"
 *              400:
 *                  description: "Invalid input"
 *              404:
 *                  description: "availability id does not exists"
 */
\App::$slim->post(
    '/availability/slots/update/',
    '\BO\Zmsapi\AvailabilitySlotsUpdate'
)
    ->setName("AvailabilitySlotsUpdate");


/**
 *  @swagger
 *  "/availability/{id}/":
 *      delete:
 *          summary: Deletes an availability
 *          tags:
 *              - availability
 *          parameters:
 *              -   name: id
 *                  description: availability number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success, returns deleted object or empty if object did not exists"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/availability.json"
 */
\App::$slim->delete(
    '/availability/{id:\d{1,11}}/',
    '\BO\Zmsapi\AvailabilityDelete'
)
    ->setName("AvailabilityDelete");

/**
 *  @swagger
 *  "/calendar/":
 *      post:
 *          summary: Get a list of available days for appointments
 *          tags:
 *              - calendar
 *          parameters:
 *              -   name: calendar
 *                  description: data for finding available days
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/calendar.json"
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: fillWithEmptyDays
 *                  description: "Returns calendar daylist including not bookable days"
 *                  in: query
 *                  type: integer
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
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/calendar.json"
 */
\App::$slim->post(
    '/calendar/',
    '\BO\Zmsapi\CalendarGet'
)
    ->setName("CalendarGet");

/**
 *  @swagger
 *  "/calldisplay/":
 *      post:
 *          summary: Get preferences for a calldisplay
 *          tags:
 *              - calldisplay
 *          parameters:
 *              -   name: calldisplay
 *                  description: data containing scopes and clusters
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/calldisplay.json"
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get an updated calldislay object with updated scope and cluster list
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/calldisplay.json"
 *              404:
 *                  description: "Could not find a given cluster or scope, see metaresult"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 */
\App::$slim->post(
    '/calldisplay/',
    '\BO\Zmsapi\CalldisplayGet'
)
    ->setName("CalldisplayGet");

/**
 *  @swagger
 *  "/calldisplay/queue/":
 *      post:
 *          summary: Get queue for a calldisplay
 *          tags:
 *              - calldisplay
 *              - queue
 *          parameters:
 *              -   name: calldisplay
 *                  description: data containing scopes and clusters
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/calldisplay.json"
 *              -   name: statusList
 *                  description: "List of statuses for displaying the associated calls in the call display"
 *                  in: query
 *                  type: array
 *                  items:
 *                      type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get a list of queue entries, return empty list if no queue was found
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/queue.json"
 *              404:
 *                  description: "Could not find a given cluster or scope or missing cluster and sopelist in entity, see metaresult"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 */
\App::$slim->post(
    '/calldisplay/queue/',
    '\BO\Zmsapi\CalldisplayQueue'
)
    ->setName("CalldisplayQueue");

/**
 *  @swagger
 *  "/cluster/{id}/":
 *      get:
 *          summary: Get an cluster by id
 *          tags:
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/cluster.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,11}}/',
    '\BO\Zmsapi\ClusterGet'
)
    ->setName("ClusterGet");

/**
 *  @swagger
 *  "/cluster/{id}/":
 *      post:
 *          summary: Update an cluster
 *          tags:
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: cluster
 *                  description: cluster data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/cluster.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/cluster.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->post(
    '/cluster/{id:\d{1,11}}/',
    '\BO\Zmsapi\ClusterUpdate'
)
    ->setName("ClusterUpdate");

/**
 *  @swagger
 *  "/cluster/{id}/":
 *      delete:
 *          summary: Deletes an cluster
 *          tags:
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->delete(
    '/cluster/{id:\d{1,11}}/',
    '\BO\Zmsapi\ClusterDelete'
)
    ->setName("ClusterDelete");

/**
 *  @swagger
 *  "/cluster/{id}/queue/next/":
 *      get:
 *          summary: Get the next process in queue by cluster id
 *          x-since: 2.11
 *          tags:
 *              - cluster
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: number of cluster
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: get a process
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
 *                  description: "Could not find a process or cluster not found"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,4}}/queue/next/',
    '\BO\Zmsapi\ProcessNextByCluster'
)
    ->setName("ProcessNextByCluster");

/**
 *  @swagger
 *  "/cluster/{id}/queue/":
 *      get:
 *          summary: Get a waiting queue for a cluster
 *          tags:
 *              - cluster
 *              - queue
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, return empty queueList if no entry was found"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/queue.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,11}}/queue/',
    '\BO\Zmsapi\ClusterQueue'
)
    ->setName("ClusterQueue");

/**
 *  @swagger
 *  "/cluster/{id}/request/":
 *      get:
 *          summary: Get a list of requests by cluster ID
 *          x-since: 2.11
 *          tags:
 *              - request
 *          parameters:
 *              -   name: id
 *                  description: number of cluster
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, might be empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/request.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,11}}/request/',
    '\BO\Zmsapi\RequestListByCluster'
)
    ->setName("RequestListByCluster");

/**
 *  @swagger
 *  "/cluster/{id}/workstation/":
 *      get:
 *          summary: Get a list of today logged in workstations by cluster ID
 *          x-since: 2.11
 *          tags:
 *              - cluster
 *              - workstation
 *          parameters:
 *              -   name: id
 *                  description: number of cluster
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, might by empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,11}}/workstation/',
    '\BO\Zmsapi\WorkstationListByCluster'
)
    ->setName("WorkstationListByCluster");

/**
 *  @swagger
 *  "/cluster/{id}/waitingnumber/{hash}/":
 *      get:
 *          summary: Get a waitingNumber according to scope preferences in cluster
 *          x-since: 2.08
 *          tags:
 *              - cluster
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  required: true
 *                  in: path
 *                  type: integer
 *              -   name: hash
 *                  description: valid ticketprinter hash
 *                  required: true
 *                  in: path
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, return exception with code 200 if ticketprinter is disabled"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/process.json"
 *              403:
 *                  description: "hash is not valid"
 *              404:
 *                  description: "cluster id does not exists, reserve process failed"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,11}}/waitingnumber/{hash}/',
    '\BO\Zmsapi\TicketprinterWaitingnumberByCluster'
)
    ->setName("TicketprinterWaitingnumberByCluster");

/**
 *  @swagger
 *  "/cluster/{id}/workstationcount/":
 *      get:
 *          summary: Get a cluster with calculated workstation count on its scopes.
 *          x-since: 2.11
 *          description: Calculating the workstation count requires performance, thus this is an extra api query
 *          tags:
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/cluster.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,4}}/workstationcount/',
    '\BO\Zmsapi\ClusterWithWorkstationCount'
)
    ->setName("ClusterWithWorkstationCount");

/**
 *  @swagger
 *  "/cluster/{id}/imagedata/calldisplay/":
 *      get:
 *          summary: get image data by cluster id for calldisplay image
 *          x-since: 2.10
 *          tags:
 *              - cluster
 *              - mimepart
 *          parameters:
 *              -   name: id
 *                  description: number of cluster
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get existing imagedata by cluster id
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mimepart.json"
 *              404:
 *                  description: "Could not find given cluster"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,4}}/imagedata/calldisplay/',
    '\BO\Zmsapi\ClusterCalldisplayImageDataGet'
)
    ->setName("ClusterCalldisplayImageDataGet");

/**
 *  @swagger
 *  "/cluster/{id}/imagedata/calldisplay/":
 *      post:
 *          summary: upload and get image data by cluster id for calldisplay image
 *          x-since: 2.10
 *          tags:
 *              - cluster
 *              - mimepart
 *          parameters:
 *              -   name: id
 *                  description: number of cluster
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: mimepart
 *                  description: mimepart image data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/mimepart.json"
 *          responses:
 *              200:
 *                  description: get an updated mimepart entity
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mimepart.json"
 *              404:
 *                  description: "Could not find given cluster"
 */
\App::$slim->post(
    '/cluster/{id:\d{1,4}}/imagedata/calldisplay/',
    '\BO\Zmsapi\ClusterCalldisplayImageDataUpdate'
)
    ->setName("ClusterCalldisplayImageDataUpdate");

/**
 *  @swagger
 *  "/cluster/{id}/imagedata/calldisplay/":
 *      delete:
 *          summary: Delete calldisplay image by cluster
 *          x-since: 2.10
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->delete(
    '/cluster/{id:\d{1,4}}/imagedata/calldisplay/',
    '\BO\Zmsapi\ClusterCalldisplayImageDataDelete'
)
    ->setName("ClusterCalldisplayImageDataDelete");

/**
 *  @swagger
 *  "/cluster/{id}/organisation/":
 *      get:
 *          summary: Get an organisation by clusterId.
 *          x-since: 2.10
 *          tags:
 *              - cluster
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/organisation.json"
 *              404:
 *                  description: "organisation or cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,4}}/organisation/',
    '\BO\Zmsapi\OrganisationByCluster'
)
    ->setName("OrganisationByCluster");

/**
 *  @swagger
 *  "/cluster/{id}/process/{date}/":
 *      get:
 *          summary: Get a list of processes by cluster and date
 *          x-since: 2.11
 *          tags:
 *              - cluster
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: date
 *                  description: day in format YYYY-MM-DD
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, also if process list is empty"
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
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/cluster/{id:\d{1,11}}/process/{date:\d\d\d\d-\d\d-\d\d}/',
    '\BO\Zmsapi\ProcessListByClusterAndDate'
)
    ->setName("ProcessListByClusterAndDate");

/**
 *  @swagger
 *  "/config/":
 *      get:
 *          summary: Get config
 *          tags:
 *              - config
 *          parameters:
 *              -   name: X-Token
 *                  description: Secure Token
 *                  required: true
 *                  in: header
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/config.json"
 *              401:
 *                  description: "authentification failed"
 */
\App::$slim->get(
    '/config/',
    '\BO\Zmsapi\ConfigGet'
)
    ->setName("ConfigGet");

/**
 *  @swagger
 *  "/config/":
 *      post:
 *          summary: update config properties
 *          tags:
 *              - config
 *          parameters:
 *              -   name: X-Token
 *                  description: Secure Token
 *                  required: true
 *                  in: header
 *                  type: string
 *              -   name: config
 *                  description: config data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/config.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/config.json"
 *              401:
 *                  description: "authentification failed"
 */
\App::$slim->post(
    '/config/',
    '\BO\Zmsapi\ConfigUpdate'
)
    ->setName("ConfigUpdate");






\App::$slim->get(
    '/mailtemplates/',
    '\BO\Zmsapi\MailTemplatesGet'
)
    ->setName("MailTemplatesGet");
    

\App::$slim->post(
    '/mailtemplates/',
    '\BO\Zmsapi\MailTemplatesUpdate'
)
    ->setName("MailTemplatesUpdate");
        
\App::$slim->post(
        '/mailtemplates-create-customization/',
        '\BO\Zmsapi\MailTemplatesCreateCustomization'
    )
        ->setName("MailTemplatesCreateCustomization");

\App::$slim->get(
        '/custom-mailtemplates/{providerId}/',
        '\BO\Zmsapi\MailCustomTemplatesGet'
    )
        ->setName("MailCustomTemplatesGet");

\App::$slim->get(
        '/merged-mailtemplates/{providerId}/',
        '\BO\Zmsapi\MailMergedTemplatesGet'
    )
        ->setName("MailMergedTemplatesGet");
            

\App::$slim->delete(
        '/mailtemplates/{templateId}/',
        '\BO\Zmsapi\MailTemplatesDelete'
    )
        ->setName("MailTemplatesDelete");
        








/**
 *  @swagger
 *  "/dayoff/{year}/":
 *      get:
 *          summary: Get a list of common free days for a given year
 *          tags:
 *              - dayoff
 *          parameters:
 *              -   name: year
 *                  description: year for the free days
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success, returns empty list if no dayoff was found"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/dayoff.json"
 *              404:
 *                  description: "year out of range"
 */
\App::$slim->get(
    '/dayoff/{year:2\d{3,3}}/',
    '\BO\Zmsapi\DayoffList'
)
    ->setName("DayoffList");

/**
 *  @swagger
 *  "/dayoff/{year}/":
 *      post:
 *          summary: Update list of common free days
 *          tags:
 *              - dayoff
 *          parameters:
 *              -   name: year
 *                  description: year for the free days
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: dayoff
 *                  description: dayoff data to update
 *                  in: body
 *                  schema:
 *                      type: array
 *                      items:
 *                          $ref: "schema/dayoff.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/dayoff.json"
 *              404:
 *                  description: "year out of range"
 */
\App::$slim->post(
    '/dayoff/{year:2\d{3,3}}/',
    '\BO\Zmsapi\DayoffUpdate'
)
    ->setName("DayoffUpdate");

/**
 *  @swagger
 *  "/department/":
 *      get:
 *          summary: Get a list of departments
 *          tags:
 *              - department
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/department.json"
 *              403:
 *                  x-since: 2.12
 *                  description: "missing or wrong access rights"
 */
\App::$slim->get(
    '/department/',
    '\BO\Zmsapi\DepartmentList'
)
    ->setName("DepartmentList");

/**
 *  @swagger
 *  "/department/{id}/":
 *      get:
 *          summary: Get an department by id
 *          tags:
 *              - department
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/department.json"
 *              404:
 *                  description: "department id does not exists"
 */
\App::$slim->get(
    '/department/{id:\d{1,11}}/',
    '\BO\Zmsapi\DepartmentGet'
)
    ->setName("DepartmentGet");

/**
 *  @swagger
 *  "/department/{id}/":
 *      post:
 *          summary: Update an department
 *          tags:
 *              - department
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: department
 *                  description: department data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/department.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/department.json"
 *              404:
 *                  description: "department id does not exists"
 */
\App::$slim->post(
    '/department/{id:\d{1,11}}/',
    '\BO\Zmsapi\DepartmentUpdate'
)
    ->setName("DepartmentUpdate");

/**
 *  @swagger
 *  "/department/{id}/":
 *      delete:
 *          summary: Deletes an department
 *          tags:
 *              - department
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "department id does not exists"
 *              428:
 *                  x-since: 2.12
 *                  description: "department has still assigned scopes or clusters"
 */
\App::$slim->delete(
    '/department/{id:\d{1,11}}/',
    '\BO\Zmsapi\DepartmentDelete'
)
    ->setName("DepartmentDelete");

/**
 *  @swagger
 *  "/department/{id}/scope/":
 *      post:
 *          summary: Add a new scope
 *          tags:
 *              - department
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: scope
 *                  description: scope data to add
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/scope.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/scope.json"
 *              404:
 *                  description: "Missing required properties in the scope"
 */
\App::$slim->post(
    '/department/{id:\d{1,11}}/scope/',
    '\BO\Zmsapi\DepartmentAddScope'
)
    ->setName("DepartmentAddScope");

/**
 *  @swagger
 *  "/department/{id}/cluster/":
 *      post:
 *          summary: Add a new cluster
 *          x-since: 2.10
 *          tags:
 *              - department
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: cluster
 *                  description: cluster data to add
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/cluster.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/cluster.json"
 *              404:
 *                  description: "Missing required properties in the cluster"
 */
\App::$slim->post(
    '/department/{id:\d{1,11}}/cluster/',
    '\BO\Zmsapi\DepartmentAddCluster'
)
    ->setName("DepartmentAddCluster");

/**
 *  @swagger
 *  "/department/{id}/organisation/":
 *      get:
 *          summary: Get the parent organisation for a department
 *          tags:
 *              - department
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/organisation.json"
 *              403:
 *                  x-since: 2.12
 *                  description: "department is not assigned to logged in useraccount"
 *              404:
 *                  x-since: 2.12
 *                  description: "department does not exist"
 */
\App::$slim->get(
    '/department/{id:\d{1,11}}/organisation/',
    '\BO\Zmsapi\OrganisationByDepartment'
)
    ->setName("OrganisationByDepartment");

/**
 *  @swagger
 *  "/department/{id}/useraccount/":
 *      get:
 *          summary: Get a list of useraccounts for a department
 *          x-since: 2.10
 *          tags:
 *              - department
 *              - useraccount
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/useraccount.json"
 *              403:
 *                  x-since: 2.12
 *                  description: "department is not assigned to logged in useraccount"
 *              404:
 *                  x-since: 2.12
 *                  description: "department does not exist"
 */
\App::$slim->get(
    '/department/{id:\d{1,11}}/useraccount/',
    '\BO\Zmsapi\DepartmentUseraccountList'
)
    ->setName("DepartmentUseraccountList");

/**
 *  @swagger
 *  "/department/{id}/workstation/":
 *      get:
 *          summary: Get a list of workstations for a department
 *          x-since: 2.11
 *          tags:
 *              - department
 *              - workstation
 *          parameters:
 *              -   name: id
 *                  description: department number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/workstation.json"
 *              403:
 *                  x-since: 2.12
 *                  description: "department is not assigned to logged in useraccount"
 *              404:
 *                  x-since: 2.12
 *                  description: "department does not exist"
 */
\App::$slim->get(
    '/department/{id:\d{1,11}}/workstation/',
    '\BO\Zmsapi\DepartmentWorkstationList'
)
    ->setName("DepartmentWorkstationList");

/**
 *  @swagger
 *  "/log/process/{id}/":
 *      get:
 *          summary: Get a list of log entries for a process
 *          x-since: 2.11
 *          tags:
 *              - process
 *              - log
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: id
 *                  description: id of a process
 *                  in: path
 *                  required: true
 *                  type: number
 *          responses:
 *              200:
 *                  description: Get a list of log entries for a process or empty list
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/log.json"
 */
\App::$slim->get(
    '/log/process/{id:\d{1,11}}/',
    '\BO\Zmsapi\ProcessLog'
)
    ->setName("ProcessLog");

/**
 *  @swagger
 *  "/log/process/{id}/":
 *      post:
 *          summary: Add a log entry for a process
 *          tags:
 *              - process
 *              - log
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: id
 *                  description: id of a process
 *                  in: path
 *                  required: true
 *                  type: number
 *              -   name: mimepart
 *                  description: mimepart data with content
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/mimepart.json"
 *          responses:
 *              200:
 *                  description: log accepted
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/mimepart.json"
 *              400:
 *                  description: "Missing required properties in the mimepart"
 *              403:
 *                  description: "Missing access rights, unvalid process id"
 */
\App::$slim->post(
    '/log/process/{id:\d{1,11}}/',
    '\BO\Zmsapi\ProcessAddLog'
)
    ->setName("ProcessAddLog");

/**
 *  @swagger
 *  "/mails/":
 *      get:
 *          summary: get a list of mails in the send queue
 *          x-since: 2.11
 *          tags:
 *              - mail
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
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
\App::$slim->get(
    '/mails/',
    '\BO\Zmsapi\MailList'
)
    ->setName("MailList");


/**
 *  @swagger
 *  "/mails/":
 *      post:
 *          summary: Add a mail to the send queue
 *          tags:
 *              - mail
 *          parameters:
 *              -   name: mail
 *                  description: mail data to send
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/mail.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: mail accepted
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/mail.json"
 *              400:
 *                  description: "Missing required properties in the notification"
 */
\App::$slim->post(
    '/mails/',
    '\BO\Zmsapi\MailAdd'
)
    ->setName("MailAdd");

/**
 *  @swagger
 *  "/mails/{id}/":
 *      delete:
 *          summary: delete a mail in the send queue
 *          tags:
 *              - mail
 *          parameters:
 *              -   name: id
 *                  description: mail number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: succesfully deleted
 *              404:
 *                  description: "could not find mail or mail already sent"
 */
\App::$slim->delete(
    '/mails/{id:\d{1,11}}/',
    '\BO\Zmsapi\MailDelete'
)
    ->setName("MailDelete");


/**
 *  @swagger
 *  "/notification/":
 *      get:
 *          summary: get a list of notifications in the send queue
 *          tags:
 *              - notification
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
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
\App::$slim->get(
    '/notification/',
    '\BO\Zmsapi\NotificationList'
)
    ->setName("NotificationList");



/**
 *  @swagger
 *  "/notification/":
 *      post:
 *          summary: Add a notification to the send queue
 *          tags:
 *              - notification
 *          parameters:
 *              -   name: notification
 *                  description: notification data to send
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/notification.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: notification accepted
 *              400:
 *                  description: "Missing required properties in the notification"
 */
\App::$slim->post(
    '/notification/',
    '\BO\Zmsapi\NotificationAdd'
)
    ->setName("NotificationAdd");

/**
 *  @swagger
 *  "/notification/{id}/":
 *      delete:
 *          summary: delete a notification in the send queue
 *          tags:
 *              - notification
 *          parameters:
 *              -   name: id
 *                  description: notification number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: succesfully deleted
 *              404:
 *                  description: "could not find notification or notification already sent"
 */
\App::$slim->delete(
    '/notification/{id:\d{1,11}}/',
    '\BO\Zmsapi\NotificationDelete'
)
    ->setName("NotificationDelete");


/**
 *  @swagger
 *  "/owner/":
 *      get:
 *          summary: Get a list of owners
 *          tags:
 *              - owner
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, might be empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/owner.json"
 */
\App::$slim->get(
    '/owner/',
    '\BO\Zmsapi\OwnerList'
)
    ->setName("OwnerList");

/**
 *  @swagger
 *  "/owner/{id}/":
 *      get:
 *          summary: Get an owner by id
 *          tags:
 *              - owner
 *          parameters:
 *              -   name: id
 *                  description: owner number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/owner.json"
 *              404:
 *                  description: "owner id does not exists"
 */
\App::$slim->get(
    '/owner/{id:\d{1,11}}/',
    '\BO\Zmsapi\OwnerGet'
)
    ->setName("OwnerGet");

/**
 *  @swagger
 *  "/owner/{id}/":
 *      post:
 *          summary: Update an owner
 *          tags:
 *              - owner
 *          parameters:
 *              -   name: id
 *                  description: owner number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: owner
 *                  description: owner data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/owner.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/owner.json"
 *              400:
 *                  x-since: 2.12
 *                  description: "Invalid input"
 *              404:
 *                  description: "owner id does not exists"
 */
\App::$slim->post(
    '/owner/{id:\d{1,11}}/',
    '\BO\Zmsapi\OwnerUpdate'
)
    ->setName("OwnerUpdate");

/**
 *  @swagger
 *  "/owner/":
 *      post:
 *          summary: Add a new owner
 *          tags:
 *              - owner
 *          parameters:
 *              -   name: owner
 *                  description: owner data to add
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/owner.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/department.json"
 *              404:
 *                  description: "Missing required properties in the owner"
 */
\App::$slim->post(
    '/owner/',
    '\BO\Zmsapi\OwnerAdd'
)
    ->setName("OwnerAdd");


/**
 *  @swagger
 *  "/owner/{id}/":
 *      delete:
 *          summary: Deletes an owner
 *          tags:
 *              - owner
 *          parameters:
 *              -   name: id
 *                  description: owner number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "owner id does not exists"
 */
\App::$slim->delete(
    '/owner/{id:\d{1,11}}/',
    '\BO\Zmsapi\OwnerDelete'
)
    ->setName("OwnerDelete");

/**
 *  @swagger
 *  "/owner/{id}/organisation/":
 *      post:
 *          summary: Add a new organisation
 *          x-since: 2.11
 *          tags:
 *              - owner
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: owner number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: organisation
 *                  description: organisation data to add
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/organisation.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/organisation.json"
 *              400:
 *                  x-since: 2.12
 *                  description: "Invalid input"
 */
\App::$slim->post(
    '/owner/{id:\d{1,11}}/organisation/',
    '\BO\Zmsapi\OwnerAddOrganisation'
)
    ->setName("OwnerAddOrganisation");


/**
 *  @swagger
 *  "/organisation/":
 *      get:
 *          summary: Get a list of organisations
 *          tags:
 *              - organisation
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, might be empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/organisation.json"
 */
\App::$slim->get(
    '/organisation/',
    '\BO\Zmsapi\OrganisationList'
)
    ->setName("OrganisationList");

/**
 *  @swagger
 *  "/organisation/{id}/":
 *      get:
 *          summary: Get an organisation by id
 *          tags:
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: organisation number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/organisation.json"
 *              404:
 *                  description: "organisation id does not exists"
 */
\App::$slim->get(
    '/organisation/{id:\d{1,11}}/',
    '\BO\Zmsapi\OrganisationGet'
)
    ->setName("OrganisationGet");

    /**
 *  @swagger
 *  "/organisation/{id}/owner/":
 *      get:
 *          summary: Get the owner for an organisation
 *          tags:
 *              - organisation
 *              - owner
 *          parameters:
 *              -   name: id
 *                  description: organisation number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/owner.json"
 *              403:
 *                  x-since: 2.12
 *                  description: "organisation is not assigned to logged in useraccount"
 *              404:
 *                  x-since: 2.12
 *                  description: "organisation does not exist"
 */
\App::$slim->get(
    '/organisation/{id:\d{1,11}}/owner/',
    '\BO\Zmsapi\OwnerByOrganisation'
)
    ->setName("OwnerByOrganisation");

/**
 *  @swagger
 *  "/organisation/{id}/":
 *      post:
 *          summary: Update an organisation
 *          tags:
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: organisation number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: organisation
 *                  description: organisation data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/organisation.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/organisation.json"
 *              404:
 *                  description: "organisation id does not exists"
 */
\App::$slim->post(
    '/organisation/{id:\d{1,11}}/',
    '\BO\Zmsapi\OrganisationUpdate'
)
    ->setName("OrganisationUpdate");

/**
 *  @swagger
 *  "/organisation/{id}/":
 *      delete:
 *          summary: Deletes an organisation
 *          tags:
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: organisation number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "organisation id does not exists"
 */
\App::$slim->delete(
    '/organisation/{id:\d{1,11}}/',
    '\BO\Zmsapi\OrganisationDelete'
)
    ->setName("OrganisationDelete");

/**
 *  @swagger
 *  "/organisation/{id}/hash/":
 *      get:
 *          summary: Get a hash to identify a ticketprinter. Usually a browser requests a hash once and stores it in a cookie.
 *          tags:
 *              - organisation
 *              - ticketprinter
 *          parameters:
 *              -   name: id
 *                  description: organisation number
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
 *                              $ref: "schema/ticketprinter.json"
 *              404:
 *                  description: "organisation id does not exists"
 */
\App::$slim->get(
    '/organisation/{id:\d{1,11}}/hash/',
    '\BO\Zmsapi\OrganisationHash'
)
    ->setName("OrganisationHash");

/**
 *  @swagger
 *  "/organisation/{id}/department/":
 *      post:
 *          summary: Add a new department
 *          tags:
 *              - organisation
 *              - department
 *          parameters:
 *              -   name: id
 *                  description: organisation number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: department
 *                  description: department data to add
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/department.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/department.json"
 *              404:
 *                  description: "Missing required properties in the department"
 */
\App::$slim->post(
    '/organisation/{id:\d{1,11}}/department/',
    '\BO\Zmsapi\OrganisationAddDepartment'
)
    ->setName("OrganisationAddDepartment");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      get:
 *          summary: Get a process
 *          tags:
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: process number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: authKey
 *                  description: authentication key or name
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/process.json"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->get(
    '/process/{id:\d{1,11}}/{authKey}/',
    '\BO\Zmsapi\ProcessGet'
)
    ->setName("ProcessGet");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/appointment/":
 *      post:
 *          summary: Update an appointment of a process
 *          tags:
 *              - process
 *              - appointment
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
 *              -   name: slotsRequired
 *                  description: "On default, the required slots are calculated by fetching preferences for a provider on how much slots each request should take. Priviliged users can change the required slots. To enable this parameter, a X-Authkey header is required."
 *                  in: query
 *                  type: integer
 *                  required: false
 *              -   name: slotType
 *                  description: "On default, the slotType is 'public'. A scope can have non public appointments for booking. This is a reserve for internal use. Only priviliged users can change the slot type. To enable this parameter, a X-Authkey header is required."
 *                  in: query
 *                  type: string
 *                  required: false
 *              -   name: appointment
 *                  description: appointment data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/appointment.json"
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
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists or Failed to reserve new appointment"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/appointment/',
    '\BO\Zmsapi\AppointmentUpdate'
)
    ->setName("AppointmentUpdate");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/ics/":
 *      get:
 *          summary: Get an ICS-File for a process
 *          tags:
 *              - process
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
\App::$slim->get(
    '/process/{id:\d{1,11}}/{authKey}/ics/',
    '\BO\Zmsapi\ProcessIcs'
)
    ->setName("ProcessIcs");


/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      post:
 *          summary: Update a process but does not send any mails or notifications on status changes
 *          description: Attention - An empty list in "requests" does not delete the associated requests as expected. To delete the requests, create a dummy request with an ID of "-1" and create a one item list with this request into the process. This is required to delete the associated requests.
 *          tags:
 *              - process
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
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *              -   name: initiator
 *                  x-since: 2.13
 *                  description: "Identifies the user initiating the request for logging purposes"
 *                  in: query
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
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/',
    '\BO\Zmsapi\ProcessUpdate'
)
    ->setName("ProcessUpdate");

    /**
 *  @swagger
 *  "/process/{id}/{authKey}/preconfirmation/mail/":
 *      post:
 *          summary: send mail on preconfirmed process. Depending on config, if no mail is send, an empty mail is returned.
 *          tags:
 *              - process
 *              - mail
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
 *                  description: process data for building mail
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mail.json"
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/preconfirmation/mail/',
    '\BO\Zmsapi\ProcessPreconfirmationMail'
)
    ->setName("ProcessPreconfirmationMail");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/confirmation/mail/":
 *      post:
 *          summary: send mail on confirmed process. Depending on config, if no mail is send, an empty mail is returned.
 *          tags:
 *              - process
 *              - mail
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
 *                  description: process data for building mail
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mail.json"
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/confirmation/mail/',
    '\BO\Zmsapi\ProcessConfirmationMail'
)
    ->setName("ProcessConfirmationMail");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/delete/mail/":
 *      post:
 *          summary: send mail on delete process. Depending on config, if no mail is send, an empty mail is returned.
 *          x-since: 2.08
 *          tags:
 *              - process
 *              - mail
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
 *                  description: process data for building mail
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mail.json"
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/delete/mail/',
    '\BO\Zmsapi\ProcessDeleteMail'
)
    ->setName("ProcessDeleteMail");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/confirmation/notification/":
 *      post:
 *          summary: send notification on confirmed process
 *          tags:
 *              - process
 *              - notification
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
 *                  description: process data for building notification
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
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
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/confirmation/notification/',
    '\BO\Zmsapi\ProcessConfirmationNotification'
)
    ->setName("ProcessConfirmationNotification");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/delete/notification/":
 *      post:
 *          summary: send notification on delete process. Depending on config, if no mail is send, an empty mail is returned.
 *          x-since: 2.11
 *          tags:
 *              - process
 *              - notification
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
 *                  description: process data for building mail
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/notification.json"
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "forbidden, authkey does not match or status changes, only data may be changed"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/process/{id:\d{1,11}}/{authKey}/delete/notification/',
    '\BO\Zmsapi\ProcessDeleteNotification'
)
    ->setName("ProcessDeleteNotification");

/**
 *  @swagger
 *  "/process/{id}/{authKey}/":
 *      delete:
 *          summary: Deletes a process but does not send any mails or notifications
 *          tags:
 *              - process
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
 *              -   name: initiator
 *                  x-since: 2.13
 *                  description: "Identifies the user initiating the request for logging purposes"
 *                  in: query
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
\App::$slim->delete(
    '/process/{id:\d{1,11}}/{authKey}/',
    '\BO\Zmsapi\ProcessDelete'
)
    ->setName("ProcessDelete");

/**
 *  @swagger
 *  "/process/{id}/":
 *      delete:
 *          summary: Deletes a process at once
 *          description: A deleted process usually waits for a cronjob to be removed. This operation deletes the process without any waitingtime. An X-Authkey is required and the workstation needs access to the scope of the process.
 *          x-since: 2.13
 *          tags:
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: process number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: initiator
 *                  description: "Identifies the user initiating the request for logging purposes"
 *                  in: query
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
 *                  description: "operation is not allowed due to access rights"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->delete(
    '/process/{id:\d{1,11}}/',
    '\BO\Zmsapi\ProcessDeleteQuick'
)
    ->setName("ProcessDeleteQuick");

/**
 *  @swagger
 *  "/process/search/":
 *      get:
 *          summary: Get a list of search results for processes
 *          x-since: 2.11
 *          tags:
 *              - process
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: query
 *                  description: "Query string for searching. Searches in process.client.*.familyName|telephone|email and process.id"
 *                  in: query
 *                  type: integer
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
 */
\App::$slim->get(
    '/process/search/',
    '\BO\Zmsapi\ProcessSearch'
)
    ->setName("ProcessSearch");


/**
 *  @swagger
 *  "/process/status/free/":
 *      post:
 *          summary: Get a list of free processes for a given day
 *          tags:
 *              - calendar
 *              - process
 *          parameters:
 *              -   name: calendar
 *                  description: data for finding available processes, try to restrict data to one day, if possible
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/calendar.json"
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: keepLessData
 *                  description: "Parameter for withLessData method to keep given values in response data"
 *                  in: query
 *                  type: array
 *                  items:
 *                      type: string
 *              -   name: groupData
 *                  description: "Set this parameter if you want to automatically group appointments by time and scope. Be aware, that this parameters reduces the number of appointments, there is no information of the original count left. The value for this parameter sets a threshold to set grouping a step further. Giving a value of 200 means, that on a result with 200 or more appointments, the grouping is not by exact time, instead the hour of the appointment is used to group the results, so that for every hour an scope, at least an available appointment is shown."
 *                  in: query
 *                  type: integer
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
 *                  description: if no process found, return empty list
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
\App::$slim->post(
    '/process/status/free/',
    '\BO\Zmsapi\ProcessFree'
)
    ->setName("ProcessFree");

/**
 *  @swagger
 *  "/process/status/reserved/":
 *      get:
 *          summary: Get a list of reserved processes
 *          tags:
 *              - process
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get a list of processes, might be empty
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
\App::$slim->get(
    '/process/status/reserved/',
    '\BO\Zmsapi\ProcessReservedList'
)
    ->setName("ProcessReservedList");

/**
 *  @swagger
 *  "/process/status/reserved/":
 *      post:
 *          summary: Try to reserve the appointments in a process
 *          tags:
 *              - process
 *          parameters:
 *              -   name: process
 *                  description: process data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *              -   name: X-Authkey
 *                  description: optional authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *                  required: false
 *              -   name: slotsRequired
 *                  description: "On default, the required slots are calculated by fetching preferences for a provider on how much slots each request should take. Priviliged users can change the required slots. To enable this parameter, a X-Authkey header is required."
 *                  in: query
 *                  type: integer
 *                  required: false
 *              -   name: clientkey
 *                  description: clientkey to identify api client
 *                  type: string
 *                  in: query
 *                  required: false
 *              -   name: slotType
 *                  description: "On default, the slotType is 'public'. A scope can have non public appointments for booking. This is a reserve for internal use. Only priviliged users can change the slot type. To enable this parameter, a X-Authkey header is required."
 *                  in: query
 *                  type: string
 *                  required: false
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get a list of processes
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/process.json"
 *              400:
 *                  description: "Invalid input"
 *              404:
 *                  description: "Failed to reserve a process"
 */
\App::$slim->post(
    '/process/status/reserved/',
    '\BO\Zmsapi\ProcessReserve'
)
    ->setName("ProcessReserve");

    /**
 *  @swagger
 *  "/process/status/preconfirmed/":
 *      post:
 *          summary: Try to preconfirmed a process, changes status from reservered to preconfirmed
 *          tags:
 *              - process
 *          parameters:
 *              -   name: process
 *                  description: process data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: process is preconfirmed, notifications and mails sent according to preferences
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
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  x-since: 2.12
 *                  description: "given process is not reserved anymore"
 */
\App::$slim->post(
    '/process/status/preconfirmed/',
    '\BO\Zmsapi\ProcessPreconfirm'
)
    ->setName("ProcessPreconfirm");


/**
 *  @swagger
 *  "/process/status/confirmed/":
 *      post:
 *          summary: Try to confirm a process, changes status from reservered to confirmed
 *          tags:
 *              - process
 *          parameters:
 *              -   name: process
 *                  description: process data to update
 *                  required: true
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
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "authkey does not match"
 *              404:
 *                  x-since: 2.12
 *                  description: "given process is not reserved anymore"
 */
\App::$slim->post(
    '/process/status/confirmed/',
    '\BO\Zmsapi\ProcessConfirm'
)
    ->setName("ProcessConfirm");

/**
 *  @swagger
 *  "/process/status/finished/":
 *      post:
 *          summary: set process to finished or pending status. (other status settings are not allowed)
 *          x-since: 2.12
 *          tags:
 *              - process
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: process
 *                  description: process data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: process has finished or pending status now
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
 *                  description: "Invalid input or missing credentials"
 *              403:
 *                  description: "authkey does not match or process scope does not match with workstation scope"
 */
\App::$slim->post(
    '/process/status/finished/',
    '\BO\Zmsapi\ProcessFinished'
)
    ->setName("ProcessFinished");

/**
 *  @swagger
 *  "/process/status/pickup/":
 *      post:
 *          summary: Find or create a process to be used to pickup documents.
 *          x-since: 2.11
 *          description: Only process.queue.number is a necessary input. But it is possible to create a full process with a given waiting number. If the process already exists, an update is only performed, if process.id and process.authkey matches. Information about the scope are taken from the workstation fetches by X-Authkey
 *          tags:
 *              - process
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: process
 *                  description: process data to create
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: you are able to call this process now
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
 *              403:
 *                  description: "authkey does not match"
 */
\App::$slim->post(
    '/process/status/pickup/',
    '\BO\Zmsapi\ProcessPickup'
)
    ->setName("ProcessPickup");

/**
 *  @swagger
 *  "/process/status/redirect/":
 *      post:
 *          summary: Find or create a process to be used to redirect documents.
 *          x-since: 2.11
 *          description: Only process.queue.number is a necessary input. But it is possible to create a full process with a given waiting number. If the process already exists, an update is only performed, if process.id and process.authkey matches. Information about the scope are taken from the workstation fetches by X-Authkey
 *          tags:
 *              - process
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: process
 *                  description: process data to create
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: you are able to call this process now
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
 *              403:
 *                  description: "authkey does not match"
 */
\App::$slim->post(
    '/process/status/redirect/',
    '\BO\Zmsapi\ProcessRedirect'
)
    ->setName("ProcessRedirect");

/**
 *  @swagger
 *  "/process/status/queued/":
 *      post:
 *          summary: set process back to queued status.
 *          description: This call reverts a missed status to its former status, this might be queued or confirmed. The process should appear in the waiting queue afterwards.
 *          x-since: 2.12
 *          tags:
 *              - process
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: process
 *                  description: process data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: process has queued status now
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
 *              403:
 *                  description: "authkey does not match"
 */
\App::$slim->post(
    '/process/status/queued/',
    '\BO\Zmsapi\ProcessQueued'
)
    ->setName("ProcessQueued");

/**
 *  @swagger
 *  "/process/{id}/":
 *      get:
 *          summary: Get a process (access restricted by X-Authkey)
 *          x-since: 2.12
 *          tags:
 *              - process
 *              - workstation
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: id
 *                  description: process number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/workstation.json"
 *              401:
 *                  description: "login required"
 *              404:
 *                  description: "process not found"
 */
\App::$slim->get(
    '/process/{id}/',
    '\BO\Zmsapi\WorkstationProcessGet'
)
    ->setName("WorkstationProcessGet");

/**
 *  @swagger
 *  "/provider/{source}/{id}/":
 *      get:
 *          summary: Get an provider by id
 *          tags:
 *              - provider
 *          parameters:
 *              -   name: source
 *                  description: provider source like 'dldb'
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: id
 *                  description: provider number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/provider.json"
 *              404:
 *                  description: "provider id for source does not exists"
 */
\App::$slim->get(
    '/provider/{source}/{id:\d{1,11}}/',
    '\BO\Zmsapi\ProviderGet'
)
    ->setName("ProviderGet");

/**
 *  @swagger
 *  "/provider/{source}/{id}/scopes/":
 *      get:
 *          summary: Get a list of scope by provider ID
 *          x-since: 2.10
 *          tags:
 *              - provider
 *              - scope
 *          parameters:
 *              -   name: source
 *                  description: provider source like 'dldb'
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: id
 *                  description: provider number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: false
 *                  description: authentication key to identify user for testing access rights. Without key, informations are shortened
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/scope.json"
 *              404:
 *                  description: "provider id does not exists"
 */
\App::$slim->get(
    '/provider/{source}/{id:\d{1,11}}/scopes/',
    '\BO\Zmsapi\ScopeListByProvider'
)
    ->setName("ScopeListByProvider");

    /**
 *  @swagger
 *  "/request/{source}/{id}/scopes/":
 *      get:
 *          summary: Get a list of scope by request ID
 *          x-since: 2.10
 *          tags:
 *              - request
 *              - scope
 *          parameters:
 *              -   name: source
 *                  description: request source like 'dldb'
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: id
 *                  description: request number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: false
 *                  description: authentication key to identify user for testing access rights. Without key, informations are shortened
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/scope.json"
 *              404:
 *                  description: "request id does not exists"
 */
\App::$slim->get(
    '/request/{source}/{id:\d{1,11}}/scopes/',
    '\BO\Zmsapi\ScopeListByRequest'
)
    ->setName("ScopeListByRequest");

/**
 *  @swagger
 *  "/provider/{source}/":
 *      get:
 *          summary: Get a list of provider by source
 *          tags:
 *              - provider
 *          parameters:
 *              -   name: source
 *                  description: provider source like 'dldb'
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: isAssigned
 *                  description: "get a list of provider that are already assigned to a scope"
 *                  in: query
 *                  type: boolean
 *              -   name: requestList
 *                  description: "get a list of provider filtered by given requests (csv-string)"
 *                  in: query
 *                  type: array
 *                  items:
 *                     type: string
 *                  collectionFormat: csv
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/provider.json"
 *              404:
 *                  description: "provider id for source does not exists"
 */
\App::$slim->get(
    '/provider/{source}/',
    '\BO\Zmsapi\ProviderList'
)
    ->setName("ProviderList");

/**
 *  @swagger
 *  "/provider/{source}/request/{csv}/":
 *      get:
 *          deprecated: true
 *          summary: DEPRECATED - use provider/{source}/ with requestList as paramter instead to get a list of provider by request numbers
 *          tags:
 *              - provider
 *          parameters:
 *              -   name: source
 *                  description: request source like 'dldb'
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: csv
 *                  required: true
 *                  description: request numbers as csv string
 *                  in: path
 *                  type: array
 *                  items:
 *                     type: string
 *                  collectionFormat: csv
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/provider.json"
 *              400:
 *                  description: "invalid tag value"
 *              404:
 *                  description: "request id for source does not exists"
 */
\App::$slim->get(
    '/provider/{source}/request/{csv:[0-9,]{3,}}/',
    '\BO\Zmsapi\ProviderByRequestList'
)
    ->setName("ProviderByRequestList");

/**
 *  @swagger
 *  "/request/{source}/{id}/":
 *      get:
 *          summary: Get an request by id
 *          tags:
 *              - request
 *          parameters:
 *              -   name: source
 *                  description: request source like 'dldb'
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: id
 *                  description: request number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/request.json"
 *              404:
 *                  description: "request id for source does not exists"
 */
\App::$slim->get(
    '/request/{source}/{id:\d{1,11}}/',
    '\BO\Zmsapi\RequestGet'
)
    ->setName("RequestGet");

/**
 *  @swagger
 *  "/provider/{source}/{id}/request/":
 *      get:
 *          summary: Get a list of requests by provider ID
 *          tags:
 *              - request
 *          parameters:
 *              -   name: source
 *                  description: name of source
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: id
 *                  description: number of provider
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/request.json"
 *              404:
 *                  description: "provider id does not exists"
 */
\App::$slim->get(
    '/provider/{source}/{id:\d{1,11}}/request/',
    '\BO\Zmsapi\RequestListByProvider'
)
    ->setName("RequestListByProvider");

/**
 *  @swagger
 *  "/scope/{id}/request/":
 *      get:
 *          summary: Get a list of requests by scope ID
 *          x-since: 2.11
 *          tags:
 *              - request
 *          parameters:
 *              -   name: id
 *                  description: number of scope
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/request.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/request/',
    '\BO\Zmsapi\RequestListByScope'
)
    ->setName("RequestListByScope");

/**
 *  @swagger
 *  "/scope/{id}/workstation/":
 *      get:
 *          summary: Get a list of today logged in workstations by scope ID
 *          x-since: 2.11
 *          tags:
 *              - scope
 *              - workstation
 *          parameters:
 *              -   name: id
 *                  description: number of scope
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, might by empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/workstation/',
    '\BO\Zmsapi\WorkstationListByScope'
)
    ->setName("WorkstationListByScope");

/**
 *  @swagger
 *  "/scope/":
 *      get:
 *          summary: Get a list of scopes
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: X-Authkey
 *                  required: false
 *                  description: authentication key to identify user for testing access rights. Without key, informations are shortened
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
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
\App::$slim->get(
    '/scope/',
    '\BO\Zmsapi\ScopeList'
)
    ->setName("ScopeList");

/**
 *  @swagger
 *  "/scope/{id}/":
 *      get:
 *          summary: Get a scope
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: false
 *                  description: authentication key to identify user for testing access rights. Without key, informations are shortened
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
\App::$slim->get(
    '/scope/{id:\d{1,11}}/',
    '\BO\Zmsapi\ScopeGet'
)
    ->setName("ScopeGet");

/**
 *  @swagger
 *  "/scope/{id}/department/":
 *      get:
 *          summary: Get a department for a scope
 *          x-since: 2.10
 *          tags:
 *              - scope
 *              - department
 *          parameters:
 *              -   name: id
 *                  description: scope id
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/department.json"
 *              404:
 *                  description: "could not find a department"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/department/',
    '\BO\Zmsapi\DepartmentByScopeId'
)
    ->setName("DepartmentByScopeId");

/**
 *  @swagger
 *  "/scope/{id}/cluster/":
 *      get:
 *          summary: Get a cluster for a scope
 *          x-since: 2.11
 *          tags:
 *              - scope
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: scope id
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, might be empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/cluster.json"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/cluster/',
    '\BO\Zmsapi\ClusterByScopeId'
)
    ->setName("ClusterByScopeId");

/**
 *  @swagger
 *  "/scope/cluster/{id}/":
 *      get:
 *          summary: Get a list of scope by cluster ID
 *          tags:
 *              - scope
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/scope.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/scope/cluster/{id:\d{1,11}}/',
    '\BO\Zmsapi\ScopeListByCluster'
)
    ->setName("ScopeListByCluster");

/**
 *  @swagger
 *  "/scope/prefered/cluster/{id}/":
 *      get:
 *          summary: Get the prefered scope with shortest Waitingtime by cluster ID
 *          x-since: 2.13
 *          tags:
 *              - scope
 *              - cluster
 *          parameters:
 *              -   name: id
 *                  description: cluster number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/scope.json"
 *              404:
 *                  description: "cluster id does not exists"
 */
\App::$slim->get(
    '/scope/prefered/cluster/{id:\d{1,11}}/',
    '\BO\Zmsapi\ScopePreferedByCluster'
)
    ->setName("ScopePreferedByCluster");

/**
 *  @swagger
 *  "/scope/{id}/availability/":
 *      get:
 *          summary: Get a list of availability entries
 *          x-since: 2.11
 *          tags:
 *              - scope
 *              - availability
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: reserveEntityIds
 *                  description: "Deprecated"
 *                  in: query
 *                  type: integer
 *              -   name: startDate
 *                  description: "only fetch availabilities starting this date"
 *                  in: query
 *                  type: string
 *              -   name: endDate
 *                  description: "only fetch availabilities before this date"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/availability.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/availability/',
    '\BO\Zmsapi\AvailabilityListByScope'
)
    ->setName("AvailabilityListByScope");

/**
 *  @swagger
 *  "/scope/{id}/conflict/":
 *      get:
 *          summary: Get a list of conflicts by scope and date
 *          x-since: 2.11
 *          tags:
 *              - scope
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: startDate
 *                  description: "only fetch availabilities starting this date"
 *                  in: query
 *                  type: string
 *              -   name: endDate
 *                  description: "only fetch availabilities before this date"
 *                  in: query
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success, also if process list is empty"
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
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/conflict/',
    '\BO\Zmsapi\ConflictListByScope'
)
    ->setName("ConflictListByScope");


/**
 *  @swagger
 *  "/scope/{id}/process/{date}/":
 *      get:
 *          summary: Get a list of processes by scope and date
 *          x-since: 2.11
 *          tags:
 *              - scope
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: date
 *                  description: day in format YYYY-MM-DD
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, also if process list is empty"
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
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/process/{date:\d\d\d\d-\d\d-\d\d}/',
    '\BO\Zmsapi\ProcessListByScopeAndDate'
)
    ->setName("ProcessListByScopeAndDate");

/**
 *  @swagger
 *  "/scope/{id}/process/status/{status}/":
 *      get:
 *          summary: Get a list of processes by scope and status (pending for example)
 *          x-since: 2.14
 *          tags:
 *              - scope
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: status
 *                  description: status of process
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/process/status/{status}/',
    '\BO\Zmsapi\ProcessListByScopeAndStatus'
)
    ->setName("ProcessListByScopeAndStatus");

/**
 *  @swagger
 *  "/client/processlist/summarymail/":
 *      get:
 *          summary: request a summary of open processes belonging to an email
 *          tags:
 *              - process
 *              - mail
 *          parameters:
 *              -   name: mail
 *                  description: "email address for which all planned processes have to be send by mail"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/mail.json"
 *              429:
 *                  description: "request repeated to often"
 */
\App::$slim->get(
    '/client/processlist/summarymail/',
    '\BO\Zmsapi\ProcessListSummaryMail'
)
    ->setName("ProcessListSummaryMail");
    
/**
 *  @swagger
 *  "/scope/{id}/emergency/":
 *      post:
 *          summary: Trigger an emergency
 *          x-since: 2.10
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/scope.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->post(
    '/scope/{id}/emergency/',
    '\BO\Zmsapi\ScopeEmergency'
)
    ->setName("ScopeEmergency");

/**
 *  @swagger
 *  "/scope/{id}/emergency/":
 *      delete:
 *          summary: Cancel an emergency
 *          x-since: 2.10
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/scope.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->delete(
    '/scope/{id}/emergency/',
    '\BO\Zmsapi\ScopeEmergencyStop'
)
    ->setName("ScopeEmergencyStop");

/**
 *  @swagger
 *  "/scope/{id}/emergency/respond/":
 *      post:
 *          summary: Respond to an emergency
 *          x-since: 2.10
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/scope.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->post(
    '/scope/{id}/emergency/respond/',
    '\BO\Zmsapi\ScopeEmergencyRespond'
)
    ->setName("ScopeEmergencyRespond");

/**
 *  @swagger
 *  "/scope/{id}/queue/next/":
 *      get:
 *          summary: Get the next process in queue by scope id
 *          x-since: 2.11
 *          tags:
 *              - scope
 *              - process
 *          parameters:
 *              -   name: id
 *                  description: number of scope
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: date
 *                  x-since: 2.13
 *                  description: "selected date string"
 *                  in: query
 *                  type: string
 *              -   name: exclude
 *                  x-since: 2.13
 *                  description: "exluded process numbers as csv string"
 *                  in: query
 *                  type: string
 *          responses:
 *              200:
 *                  description: get a process
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
 *                  description: "Could not find a process or scope not found"
 */
\App::$slim->get(
    '/scope/{id:\d{1,4}}/queue/next/',
    '\BO\Zmsapi\ProcessNextByScope'
)
    ->setName("ProcessNextByScope");

/**
 *  @swagger
 *  "/scope/{id}/queue/{number}/":
 *      get:
 *          summary: Get a process by queue number and scope id
 *          x-since: 2.10
 *          tags:
 *              - scope
 *              - process
 *          parameters:
 *              -   name: number
 *                  description: waitingnumber in scope for a process
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: id
 *                  description: number of scope
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get a process
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
 *                  description: "Could not find a process or scope not found"
 */
\App::$slim->get(
    '/scope/{id:\d{1,4}}/queue/{number:\d{1,10}}/',
    '\BO\Zmsapi\ProcessByQueueNumber'
)
    ->setName("ProcessByQueueNumber");

/**
 *  @swagger
 *  "/scope/{id}/imagedata/calldisplay/":
 *      get:
 *          summary: get image data by scope id for calldisplay image
 *          x-since: 2.10
 *          tags:
 *              - scope
 *              - mimepart
 *          parameters:
 *              -   name: id
 *                  description: number of scope
 *                  in: path
 *                  required: true
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get existing imagedata by scope id
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mimepart.json"
 *              404:
 *                  description: "Could not find given scope"
 */
\App::$slim->get(
    '/scope/{id:\d{1,4}}/imagedata/calldisplay/',
    '\BO\Zmsapi\ScopeCalldisplayImageDataGet'
)
    ->setName("ScopeCalldisplayImageDataGet");

/**
 *  @swagger
 *  "/scope/{id}/imagedata/calldisplay/":
 *      post:
 *          summary: upload and get image data by scope id for calldisplay image
 *          x-since: 2.10
 *          tags:
 *              - scope
 *              - mimepart
 *          parameters:
 *              -   name: id
 *                  description: number of scope
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: mimepart
 *                  description: mimepart image data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/mimepart.json"
 *          responses:
 *              200:
 *                  description: get an updated mimepart entity
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/mimepart.json"
 *              404:
 *                  description: "Could not find given scope"
 */
\App::$slim->post(
    '/scope/{id:\d{1,4}}/imagedata/calldisplay/',
    '\BO\Zmsapi\ScopeCalldisplayImageDataUpdate'
)
    ->setName("ScopeCalldisplayImageDataUpdate");

/**
 *  @swagger
 *  "/scope/{id}/imagedata/calldisplay/":
 *      delete:
 *          summary: Delete calldisplay image by scope
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->delete(
    '/scope/{id:\d{1,4}}/imagedata/calldisplay/',
    '\BO\Zmsapi\ScopeCalldisplayImageDataDelete'
)
    ->setName("ScopeCalldisplayImageDataDelete");

/**
 *  @swagger
 *  "/scope/{id}/organisation/":
 *      get:
 *          summary: Get an organisation by scopeId.
 *          tags:
 *              - scope
 *              - organisation
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/organisation.json"
 *              404:
 *                  description: "organisation or scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,4}}/organisation/',
    '\BO\Zmsapi\OrganisationByScope'
)
    ->setName("OrganisationByScope");

/**
 *  @swagger
 *  "/scope/{id}/queue/":
 *      get:
 *          summary: Get a waiting queue for a scope
 *          tags:
 *              - scope
 *              - queue
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  x-since: 2.12
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success, return empty queueList if no entry was found"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/queue.json"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/queue/',
    '\BO\Zmsapi\ScopeQueue'
)
    ->setName("ScopeQueue");

/**
 *  @swagger
 *  "/scope/{id}/ghostworkstation/":
 *      post:
 *          summary: set selected amount of ghostworkstations in workstation scope
 *          x-since: 2.12
 *          tags:
 *              - scope
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
 *                  description: get updated ghostworkstation count
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/scope.json"
 *              404:
 *                  description: "Could not find workstation scope"
 */
\App::$slim->post(
    '/scope/{id:\d{1,4}}/ghostworkstation/',
    '\BO\Zmsapi\CounterGhostWorkstation'
)
    ->setName("CounterGhostWorkstation");

/**
 *  @swagger
 *  "/scope/{id}/workstationcount/":
 *      get:
 *          summary: Get a scope with calculated workstation count.
 *          x-since: 2.11
 *          description: Calculating the workstation count requires performance, thus this is an extra api query
 *          tags:
 *              - scope
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
\App::$slim->get(
    '/scope/{id:\d{1,4}}/workstationcount/',
    '\BO\Zmsapi\ScopeWithWorkstationCount'
)
    ->setName("ScopeWithWorkstationCount");

/**
 *  @swagger
 *  "/scope/{id}/":
 *      post:
 *          summary: Update a scope
 *          tags:
 *              - scope
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
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/scope.json"
 *              400:
 *                  description: "Invalid input"
 *              404:
 *                  description: "process id does not exists"
 */
\App::$slim->post(
    '/scope/{id:\d{1,11}}/',
    '\BO\Zmsapi\ScopeUpdate'
)
    ->setName("ScopeUpdate");

/**
 *  @swagger
 *  "/scope/{id}/":
 *      delete:
 *          summary: Delete a scope
 *          tags:
 *              - scope
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  in: path
 *                  required: true
 *                  type: integer
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->delete(
    '/scope/{id:\d{1,11}}/',
    '\BO\Zmsapi\ScopeDelete'
)
    ->setName("ScopeDelete");

/**
 *  @swagger
 *  "/scope/{id}/waitingnumber/{hash}/":
 *      get:
 *          summary: Get a waitingNumber according to scope preferences
 *          tags:
 *              - scope
 *              - process
 *              - ticketprinter
 *          parameters:
 *              -   name: id
 *                  description: scope number
 *                  required: true
 *                  in: path
 *                  type: integer
 *              -   name: hash
 *                  description: valid ticketprinter hash
 *                  required: true
 *                  in: path
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/process.json"
 *              403:
 *                  description: "hash is not valid"
 *              404:
 *                  description: "scope id does not exists"
 */
\App::$slim->get(
    '/scope/{id:\d{1,11}}/waitingnumber/{hash}/',
    '\BO\Zmsapi\TicketprinterWaitingnumberByScope'
)
    ->setName("TicketprinterWaitingnumberByScope");

/**
 *  @swagger
 *  "/scope/{ids}/ticketprinter/":
 *      get:
 *          summary: Get a list of ticketprinter by scope id list
 *          x-since: 2.10
 *          tags:
 *              - scope
 *              - ticketprinter
 *          parameters:
 *              -   name: ids
 *                  description: number of scopes as csv
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  x-since: 2.12
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: get a ticketprinter collection
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/ticketprinter.json"
 *              404:
 *                  description: "no ticketprinter found"
 */
\App::$slim->get(
    '/scope/{ids}/ticketprinter/',
    '\BO\Zmsapi\TicketprinterListByScopeList'
)
    ->setName("TicketprinterListByScopeList");

/**
 *  @swagger
 *  "/session/{name}/{id}/":
 *      get:
 *          summary: Get current Session
 *          tags:
 *              - session
 *          parameters:
 *              -   name: name
 *                  description: name from session (3 - 20 letters)
 *                  required: true
 *                  in: path
 *                  type: string
 *              -   name: id
 *                  description: id from session (20 - 40 chars)
 *                  required: true
 *                  in: path
 *                  type: string
 *              -   name: sync
 *                  description: "Set this to 1 if you want synchronous read for the session data. If session data is written shortly before reading, replication might not be up to date. Using this parameter solves this problem but might result in slower responses. Use this parameter only if necessary, for example after HTTP-redirect which write session data and a read requests follows much less than a second after. Usually, two requests do not follow on each other so that this parameter is necessary."
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get a session by id and name
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/session.json"
 *              404:
 *                  description: "Could not find any available session"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/session.json"
 */
\App::$slim->get(
    '/session/{name:[a-zA-Z]{3,20}}/{id:[a-z0-9]{8,40}}/',
    '\BO\Zmsapi\SessionGet'
)
    ->setName("SessionGet");

/**
 *  @swagger
 *  "/session/":
 *      post:
 *          summary: Update current Session
 *          tags:
 *              - session
 *          parameters:
 *              -   name: session
 *                  description: session content
 *                  in: body
 *                  required: true
 *                  schema:
 *                      $ref: "schema/session.json"
 *          responses:
 *              200:
 *                  description: get an updated or new created session object
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/session.json"
 */
\App::$slim->post(
    '/session/',
    '\BO\Zmsapi\SessionUpdate'
)
    ->setName("SessionUpdate");

/**
 *  @swagger
 *  "/session/{name}/{id}/":
 *      delete:
 *          summary: delete a session
 *          tags:
 *              - session
 *          parameters:
 *              -   name: name
 *                  description: name from session (3 - 20 letters)
 *                  required: true
 *                  in: path
 *                  type: string
 *              -   name: id
 *                  description: id from session (20 - 40 chars)
 *                  required: true
 *                  in: path
 *                  type: string
 *          responses:
 *              200:
 *                  description: session deleted successfully
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/session.json"
 *              404:
 *                  description: "Could not find any available session"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/session.json"
 */
\App::$slim->delete(
    '/session/{name:[a-zA-Z]{3,20}}/{id:[a-z0-9]{20,40}}/',
    '\BO\Zmsapi\SessionDelete'
)
    ->setName("SessionDelete");

/**
 *  @swagger
 *  "/source/":
 *      get:
 *          summary: Get ist of sources
 *          tags:
 *              -   source
 *          parameters:
 *              -   name: resolveReferences
 *                  description: "Resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/source.json"
 *              404:
 *                  description: "Could not find any available source"
 */
\App::$slim->get(
    '/source/',
    '\BO\Zmsapi\SourceList'
)
    ->setName("SourceList");

/**
 *  @swagger
 *  "/source/{source}/":
 *      get:
 *          summary: Get source by name
 *          tags:
 *              - source
 *          parameters:
 *              -   name: source
 *                  description: optional name from source like dldb for example (3 - 20 letters)
 *                  required: true
 *                  in: path
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references"
 *                  in: query
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
 *                              $ref: "schema/source.json"
 *              404:
 *                  description: "Could not find any available source"
 */
\App::$slim->get(
    '/source/{source:[a-zA-Z0-9]{3,20}}/',
    '\BO\Zmsapi\SourceGet'
)
    ->setName("SourceGet");

/**
 *  @swagger
 *  "/source/":
 *      post:
 *          summary: Update a source
 *          tags:
 *              -   source
 *          parameters:
 *              -   name: source
 *                  description: source content
 *                  in: body
 *                  required: true
 *                  schema:
 *                      $ref: "schema/source.json"
 *              -   name: resolveReferences
 *                  description: "Resolve references"
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: get an updated or new created source object
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/source.json"
 */
\App::$slim->post(
    '/source/',
    '\BO\Zmsapi\SourceUpdate'
)
    ->setName("SourceUpdate");

/**
 *  @swagger
 *  "/status/":
 *      get:
 *          summary: Get status of api
 *          tags:
 *              - status
 *          parameters:
 *              -   name: includeProcessStats
 *                  description: "Collecting stats about processes slows the request down. For healthcheck, this data might not be necessary. Default is to include the stats, a value of 0 skip the stats."
 *                  in: query
 *                  type: integer
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      $ref: "schema/status.json"
 */
\App::$slim->get(
    '/status/',
    '\BO\Zmsapi\StatusGet'
)
    ->setName("StatusGet");

/**
 *  @swagger
 *  "/status/deadlock/":
 *      get:
 *          summary: Example status on a database deadlock
 *          description: Use this route if you want to test deadlock handling on a client
 *          tags:
 *              - status
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      $ref: "schema/status.json"
 */
\App::$slim->get(
    '/status/deadlock/',
    '\BO\Zmsapi\StatusDeadlock'
)
    ->setName("StatusDeadlock");

/**
 *  @swagger
 *  "/status/locktimeout/":
 *      get:
 *          summary: Example status on a database locktimeout
 *          description: Use this route if you want to test lock timeout handling on a client
 *          tags:
 *              - status
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      $ref: "schema/status.json"
 */
\App::$slim->get(
    '/status/locktimeout/',
    '\BO\Zmsapi\StatusLocktimeout'
)
    ->setName("StatusLocktimeout");

/**
 *  @swagger
 *  "/ticketprinter/{hash}/":
 *      get:
 *          summary: Get current Ticketprinter by hash
 *          tags:
 *              - ticketprinter
 *          parameters:
 *              -   name: hash
 *                  description: hash from ticketprinter
 *                  required: true
 *                  in: path
 *                  type: string
 *          responses:
 *              200:
 *                  description: get a ticketprinter by his hash
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/ticketprinter.json"
 *              404:
 *                  description: "Could not find any available ticketprinter"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/ticketprinter.json"
 */
\App::$slim->get(
    '/ticketprinter/{hash:[a-z0-9]{20,40}}/',
    '\BO\Zmsapi\TicketprinterGet'
)
    ->setName("TicketprinterGet");

/**
 *  @swagger
 *  "/ticketprinter/":
 *      post:
 *          summary: Update ticketprinter with list of scope, cluster or link buttons
 *          tags:
 *              - ticketprinter
 *          parameters:
 *              -   name: ticketprinter
 *                  description: ticketprinter data for update
 *                  in: body
 *                  required: true
 *                  schema:
 *                      $ref: "schema/ticketprinter.json"
 *          responses:
 *              200:
 *                  description: get an updated ticketprinter object
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/ticketprinter.json"
 *              400:
 *                  description: "Invalid input"
 *              403:
 *                  description: "hash is not valid"
 *              404:
 *                  description: "Could not find any available ticketprinter"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/ticketprinter.json"
 */
\App::$slim->post(
    '/ticketprinter/',
    '\BO\Zmsapi\Ticketprinter'
)
    ->setName("Ticketprinter");

/**
 *  @swagger
 *  "/useraccount/":
 *      get:
 *          summary: Get a list of useraccounts
 *          tags:
 *              - useraccount
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
 *                  type: integer
 *              -   name: right
 *                  x-since: 2.13
 *                  description: "Only fetch users with the given right like 'superuser'"
 *                  in: query
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success, might be empty"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              type: array
 *                              items:
 *                                  $ref: "schema/useraccount.json"
 *              401:
 *                  description: "login required"
 *                  x-since: 2.12
 *              403:
 *                  description: "missing or wrong access rights"
 *                  x-since: 2.12
 */
\App::$slim->get(
    '/useraccount/',
    '\BO\Zmsapi\UseraccountList'
)
    ->setName("UseraccountList");

/**
 *  @swagger
 *  "/useraccount/{loginname}/":
 *      get:
 *          summary: Get an useraccount by loginname
 *          tags:
 *              - useraccount
 *          parameters:
 *              -   name: loginname
 *                  description: useraccount number
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/useraccount.json"
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->get(
    '/useraccount/{loginname}/',
    '\BO\Zmsapi\UseraccountGet'
)
    ->setName("UseraccountGet");

/**
 *  @swagger
 *  "/useraccount/":
 *      post:
 *          summary: add a new useraccount
 *          tags:
 *              - useraccount
 *          parameters:
 *              -   name: useraccount
 *                  description: useraccount data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/useraccount.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/useraccount.json"
 *              404:
 *                  description: "Missing required properties in the useraccount"
 */
\App::$slim->post(
    '/useraccount/',
    '\BO\Zmsapi\UseraccountAdd'
)
    ->setName("UseraccounAdd");

/**
 *  @swagger
 *  "/useraccount/{loginname}/":
 *      post:
 *          summary: Update an useraccount
 *          tags:
 *              - useraccount
 *          parameters:
 *              -   name: loginname
 *                  description: useraccount number
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: useraccount
 *                  description: useraccount data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/useraccount.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/useraccount.json"
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->post(
    '/useraccount/{loginname}/',
    '\BO\Zmsapi\UseraccountUpdate'
)
    ->setName("UseraccountUpdate");

/**
 *  @swagger
 *  "/useraccount/{loginname}/":
 *      delete:
 *          summary: Deletes an useraccount
 *          tags:
 *              - useraccount
 *          parameters:
 *              -   name: loginname
 *                  description: useraccount number
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *          responses:
 *              200:
 *                  description: "success"
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->delete(
    '/useraccount/{loginname}/',
    '\BO\Zmsapi\UseraccountDelete'
)
    ->setName("UseraccountDelete");

/**
 *  @swagger
 *  "/warehouse/":
 *      get:
 *          summary: Get a list of available subjects
 *          x-since: 2.15
 *          tags:
 *              - exchange
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/exchange.json"
 *              404:
 *                  description: "report does not exists"
 */
\App::$slim->get(
    '/warehouse/',
    '\BO\Zmsapi\WarehouseSubjectListGet'
)
    ->setName("WarehouseSubjectListGet");

/**
 *  @swagger
 *  "/warehouse/{subject}/":
 *      get:
 *          summary: Get a list of available subject IDs
 *          x-since: 2.15
 *          tags:
 *              - exchange
 *          parameters:
 *              -   name: subject
 *                  description: The subject is the name of the data set
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/exchange.json"
 *              404:
 *                  description: "report does not exists"
 */
\App::$slim->get(
    '/warehouse/{subject}/',
    '\BO\Zmsapi\WarehouseSubjectGet'
)
    ->setName("WarehouseSubjectGet");

/**
 *  @swagger
 *  "/warehouse/{subject}/{subjectId}/":
 *      get:
 *          summary: Get a list of available time periods on subject
 *          x-since: 2.15
 *          tags:
 *              - exchange
 *          parameters:
 *              -   name: subject
 *                  description: The subject is the name of the data set
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: subjectId
 *                  description: A reference ID for the subject, an "_" means no ID necessary
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/exchange.json"
 *              404:
 *                  description: "report does not exists"
 */
\App::$slim->get(
    '/warehouse/{subject}/{subjectId}/',
    '\BO\Zmsapi\WarehousePeriodListGet'
)
    ->setName("WarehousePeriodListGet");

/**
 *  @swagger
 *  "/warehouse/{subject}/{subjectId}/{period}/":
 *      get:
 *          summary: Get a set of data for statistical usage
 *          x-since: 2.15
 *          tags:
 *              - exchange
 *          parameters:
 *              -   name: subject
 *                  description: The subject is the name of the data set
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: subjectId
 *                  description: A reference ID for the subject, an "_" means no ID necessary
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: period
 *                  description: A string referring to a date period. A "_" if no period is necessary, a single year like "2017" for getting monthly cumlative reports, a "2017-11" for getting daily cumulative reports and "2017-11-11" for getting hourly reports
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/exchange.json"
 *              404:
 *                  description: "report does not exists"
 */
\App::$slim->get(
    '/warehouse/{subject}/{subjectId}/{period}/',
    '\BO\Zmsapi\WarehousePeriodGet'
)
    ->setName("WarehousePeriodGet");

/**
 *  @swagger
 *  "/workstation/":
 *      get:
 *          summary: Get the current workstation based on authkey
 *          tags:
 *              - workstation
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/workstation.json"
 *              401:
 *                  description: "login required"
 */
\App::$slim->get(
    '/workstation/',
    '\BO\Zmsapi\WorkstationGet'
)
    ->setName("WorkstationGet");

/**
 *  @swagger
 *  "/workstation/":
 *      post:
 *          summary: Update a workstation, e.g. to change the scope
 *          tags:
 *              - workstation
 *          parameters:
 *              -   name: workstation
 *                  description: workstation data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/workstation.json"
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/workstation.json"
 *              401:
 *                  description: "login required"
 *              404:
 *                  description: "useraccount loginname does not exists"
 *                  x-since: 2.12
 */
\App::$slim->post(
    '/workstation/',
    '\BO\Zmsapi\WorkstationUpdate'
)
    ->setName("WorkstationUpdate");

/**
 *  @swagger
 *  "/workstation/password/":
 *      post:
 *          operationId: WorkstationPassword
 *          summary: Change the password and/or username of a useraccount
 *          x-since: 2.10
 *          tags:
 *              - workstation
 *          parameters:
 *              -   name: useraccount
 *                  description: useraccount data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/useraccount.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/useraccount.json"
 *              401:
 *                  description: "invalid credentials"
 *                  x-since: 2.12
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->post(
    '/workstation/password/',
    '\BO\Zmsapi\WorkstationPassword'
)
    ->setName('WorkstationPassword');

/**
 *  @swagger
 *  "/workstation/oauth/":
 *      post:
 *          summary: Create a workstation for an username, used to oauth
 *          tags:
 *              - workstation
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->post(
    '/workstation/oauth/',
    '\BO\Zmsapi\WorkstationOAuth'
)
    ->setName("WorkstationOAuth");


/**
 *  @swagger
 *  "/workstation/login/":
 *      post:
 *          summary: Create a workstation for an username, used to login
 *          tags:
 *              - workstation
 *          parameters:
 *              -   name: useraccount
 *                  description: useraccount data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/useraccount.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->post(
    '/workstation/login/',
    '\BO\Zmsapi\WorkstationLogin'
)
    ->setName("WorkstationLogin");

/**
 *  @swagger
 *  "/workstation/login/{loginname}/":
 *      delete:
 *          operationId: WorkstationDelete
 *          summary: Logout a user and delete his workstation entry
 *          tags:
 *              - workstation
 *          parameters:
 *              -   name: loginname
 *                  description: useraccount number
 *                  in: path
 *                  required: true
 *                  type: string
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
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
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "useraccount loginname does not exists"
 */
\App::$slim->delete(
    '/workstation/login/{loginname}/',
    '\BO\Zmsapi\WorkstationDelete'
)
    ->setName("WorkstationDelete");

/**
 *  @swagger
 *  "/workstation/process/called/":
 *      post:
 *          summary: Set a process to status called and assign to workstation
 *          x-since: 2.11
 *          tags:
 *              - workstation
 *              - process
 *          parameters:
 *              -   name: process
 *                  x-since: 2.13
 *                  description: process data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/process.json"
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "process does not exists"
 */
\App::$slim->post(
    '/workstation/process/called/',
    '\BO\Zmsapi\WorkstationProcess'
)
    ->setName("WorkstationProcess");

/**
 *  @swagger
 *  "/workstation/process/pickup/":
 *      get:
 *          summary: Get a list of processes with pending status by workstation scope or cluster scopes in clusterEnabled
 *          x-since: 2.12
 *          tags:
 *              - process
 *              - workstation
 *          parameters:
 *              -   name: X-Authkey
 *                  required: true
 *                  description: authentication key to identify user for testing access rights
 *                  in: header
 *                  type: string
 *              -   name: resolveReferences
 *                  description: "Resolve references with $ref, which might be faster on the server side. The value of the parameter is the number of iterations to resolve references"
 *                  in: query
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
 *                              type: array
 *                              items:
 *                                  $ref: "schema/process.json"
 *              401:
 *                  description: "login required"
 *              404:
 *                  description: "scope or cluster id does not exists"
 */
\App::$slim->get(
    '/workstation/process/pickup/',
    '\BO\Zmsapi\Pickup'
)
    ->setName("Pickup");

/**
 *  @swagger
 *  "/workstation/process/waitingnumber/":
 *      post:
 *          summary: Get a waitingNumber according to workstations scope or cluster
 *          x-since: 2.11
 *          tags:
 *              - workstation
 *              - process
 *          parameters:
 *              -   name: workstation
 *                  description: workstation with process data to update
 *                  required: true
 *                  in: body
 *                  schema:
 *                      $ref: "schema/workstation.json"
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
 *              401:
 *                  description: "login required"
 *                  x-since: 2.12
 *              404:
 *                  description: "scope or cluster not found"
 */
\App::$slim->post(
    '/workstation/process/waitingnumber/',
    '\BO\Zmsapi\WorkstationProcessWaitingnumber'
)
    ->setName("WorkstationProcessWaitingnumber");

/**
 *  @swagger
 *  "/workstation/process/":
 *      delete:
 *          summary: Remove a process from workstation
 *          tags:
 *              - workstation
 *              - process
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "process does not exists"
 */
\App::$slim->delete(
    '/workstation/process/',
    '\BO\Zmsapi\WorkstationProcessDelete'
)
    ->setName("WorkstationProcessDelete");

/**
 *  @swagger
 *  "/workstation/process/parked/":
 *      delete:
 *          summary: Park a process from workstation
 *          tags:
 *              - workstation
 *              - process
 *          responses:
 *              200:
 *                  description: "success"
 *                  schema:
 *                      type: object
 *                      properties:
 *                          meta:
 *                              $ref: "schema/metaresult.json"
 *                          data:
 *                              $ref: "schema/workstation.json"
 *              404:
 *                  description: "process does not exists"
 */
\App::$slim->delete(
    '/workstation/process/parked/',
    '\BO\Zmsapi\WorkstationProcessParked'
)
    ->setName("WorkstationProcessParked");

/* ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------*/

\App::$slim->get(
    '/healthcheck/',
    '\BO\Zmsapi\Healthcheck'
)
    ->setName("healthcheck");