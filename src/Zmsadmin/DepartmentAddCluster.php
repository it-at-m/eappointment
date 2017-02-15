<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class DepartmentAddCluster extends BaseController
{
    /**
     * @return String
     */
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $parentId = Validator::value($args['departmentId'])->isNumber()->getValue();
        $input = $request->getParsedBody();
        try {
            $entity = new Entity($input);
            $entity = \App::$http->readPostResult('/department/'. $parentId .'/cluster/', $entity)
                    ->getEntity();
            return Helper\Render::redirect(
                'cluster',
                array(
                    'clusterId' => $entity->id,
                    'departmentId' => $parentId,
                ),
                array(
                    'success' => 'cluster_created'
                )
            );
        } catch (\Exception $exception) {
            return Helper\Render::error($request, $exception);
        }
    }
}
