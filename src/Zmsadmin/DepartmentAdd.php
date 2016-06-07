<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Department as Entity;

/**
  * Handle requests concerning services
  *
  */
class DepartmentAdd extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $input = $request->getParsedBody();
            if (array_key_exists('save', $input)) {
                $entity = new Entity($input);
                $department = \App::$http->readPostResult(
                    '/department/'. $entity->id .'/',
                    $entity
                )->getEntity();
                return \BO\Slim\Render::redirect(
                    'department',
                    array(
                        'id' => $department->id
                    ),
                    array()
                );
            }
        } catch (\Exception $exception) {
            return \BO\Zmsappointment\Helper\Render::error($exception);
        }

        return \BO\Slim\Render::withHtml($response, 'page/department.twig', array(
            'title' => 'Standort',
            'action' => 'add',
            'menuActive' => 'owner'
        ));
    }
}
