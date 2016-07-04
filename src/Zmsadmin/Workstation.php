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
class Workstation extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $entity = \App::$http->readGetResult('/workstation/')->getEntity();
        if (!$entity->hasId()) {
            return Helper\Render::checkedRedirect(
                self::$errorHandler,
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/workstation.twig',
            array(
                'title' => 'Sachbearbeiter',
                'menuActive' => 'workstation',
                'workstation' => $entity->getArrayCopy()
            )
        );
    }
}
