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
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            \App::$http->readDeleteResult(
                '/department/'. $args['id'] .'/'
            )->getEntity();
            return Helper\Render::redirect(
                'owner',
                array(),
                array(
                    'success' => 'department_deleted'
                )
            );
        } catch (\Exception $exception) {
            return Helper\Render::error($exception);
        }
    }
}
