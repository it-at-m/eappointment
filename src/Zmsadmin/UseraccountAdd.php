<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Useraccount as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class UseraccountAdd extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $args = null;
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $input = $request->getParsedBody();
        $ownerList = \App::$http->readGetResult('/owner/')->getCollection();

        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = new Entity($input);
            $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
            return Helper\Render::redirect(
                'useraccount',
                array(
                    'id' => $entity->id
                ),
                array(
                    'success' => 'useraccount_created'
                )
            );
        }

        return \BO\Slim\Render::withHtml($response, 'page/useraccountEdit.twig', array(
            'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
            'workstation' => $workstation,
            'action' => 'add',
            'title' => 'Nutzer: Einrichtung und Administration','menuActive' => 'useraccount'
        ));
    }
}
