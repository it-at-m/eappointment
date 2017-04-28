<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class PickupDeleteAll extends BaseController
{
    /**
     * @return String
     */
     public function readResponse(
         \Psr\Http\Message\RequestInterface $request,
         \Psr\Http\Message\ResponseInterface $response,
         array $args
     ) {
         $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
         \App::$http->readDeleteResult('/pickup/');

         \BO\Slim\Render::redirect(
             'pickup',
             array('deleted' => 1)
         );
     }
}
