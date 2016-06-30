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
class Logout extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        return Helper\Render::checkedRedirect(
            self::$errorHandler,
            'login',
            array(
                'title' => 'Anmeldung'
            )
        );
    }
}
