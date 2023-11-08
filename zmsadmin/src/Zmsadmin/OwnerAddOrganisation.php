<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Organisation as Entity;
use BO\Mellon\Validator;

class OwnerAddOrganisation extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $input = $request->getParsedBody();
        $parentId = Validator::value($args['id'])->isNumber()->getValue();
        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity = \App::$http->readPostResult('/owner/'. $parentId .'/organisation/', $entity)
                ->getEntity();
            return \BO\Slim\Render::redirect(
                'organisation',
                array(
                    'id' => $entity->id
                ),
                array(
                    'success' => 'organisation_created'
                )
            );
        }

        return \BO\Slim\Render::withHtml($response, 'page/organisation.twig', array(
            'title' => 'Kunde',
            'action' => 'add',
            'menuActive' => 'organisation',
            'workstation' => $workstation
        ));
    }
}
