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
class Owner extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        Helper\Render::checkedHtml(self::$errorHandler, $response, 'page/owner.twig', array(
            'title' => 'Behörden und Standorte',
            'menuActive' => 'owner',
        ));
    }
}
