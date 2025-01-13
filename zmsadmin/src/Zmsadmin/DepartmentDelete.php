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
class DepartmentDelete extends BaseController
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
        $entityId = Validator::value($args['id'])->isNumber()->getValue();

        $entity = \App::$http->readGetResult('/department/' . $entityId . '/')->getEntity();
        $departmentName = $entity->name;


        \App::$http->readDeleteResult(
            '/department/' . $entityId . '/'
        )->getEntity();
        return \BO\Slim\Render::redirect(
            'owner_overview',
            array(),
            array(
                'success' => 'department_deleted',
                'departmentName' => $departmentName            )
        );
    }
}
