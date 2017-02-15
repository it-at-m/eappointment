<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Owner as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class OwnerAdd extends BaseController
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
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            try {
                $entity = new Entity($input);
                $entity = \App::$http->readPostResult('/owner/add/', $entity)
                    ->getEntity();
                return Helper\Render::redirect(
                    'owner',
                    array(
                        'id' => $entity->id
                    ),
                    array(
                        'success' => 'owner_created'
                    )
                );
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return \BO\Slim\Render::withHtml($response, 'page/owner.twig', array(
            'title' => 'Kunde',
            'action' => 'add',
            'menuActive' => 'owner',
            'workstation' => $workstation
        ));
    }
}
