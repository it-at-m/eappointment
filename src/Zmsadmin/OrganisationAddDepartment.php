<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class OrganisationAddDepartment extends BaseController
{
    /**
     * @return String
     */
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $input = $request->getParsedBody();
        $parentId = Validator::value($args['id'])->isNumber()->getValue();
        if (is_array($input) && array_key_exists('save', $input)) {
            try {
                $entity = new Entity($input);
                $department = \App::$http->readPostResult('/organisation/'. $parentId .'/department/', $entity)
                    ->getEntity();
                return Helper\Render::redirect(
                    'department',
                    array(
                        'id' => $department->id
                    ),
                    array(
                        'success' => 'department_created'
                    )
                );
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return \BO\Slim\Render::withHtml($response, 'page/department.twig', array(
            'title' => 'Standort',
            'action' => 'add',
            'menuActive' => 'owner',
            'workstation' => $workstation
        ));
    }
}
