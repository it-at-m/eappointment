<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Owner as Entity;
use BO\Mellon\Validator;

class OwnerAdd extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity = \App::$http->readPostResult('/owner/add/', $entity)
                ->getEntity();
            return \BO\Slim\Render::redirect(
                'owner',
                array(
                    'id' => $entity->id
                ),
                array(
                    'success' => 'owner_created'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/owner.twig',
            array(
                'title' => 'Kunde',
                'action' => 'add',
                'menuActive' => 'owner',
                'workstation' => $workstation
            )
        );
    }
}
