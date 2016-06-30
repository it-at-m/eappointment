<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class OwnerOverview extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences'=>4))->getCollection();
        $organisationList = $ownerList->getOrganisationsByOwnerId(23);
        if (!count($ownerList)) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/ownerOverview.twig',
            array(
                'title' => 'Behörden und Standorte',
                'menuActive' => 'owner',
                'itemList' => $organisationList,
            )
        );
    }
}
