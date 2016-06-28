<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Scope as Entity;

/**
  * Handle requests concerning services
  *
  */
class ScopeDelete extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $entityId = Validator::value($args['id'])->isNumber()->getValue();

        try {
            \App::$http->readDeleteResult(
                '/scope/'. $entityId .'/'
            )->getEntity();
            return Helper\Render::redirect(
                'owner_overview',
                array(),
                array(
                    'success' => 'scope_deleted'
                )
            );
        } catch (\Exception $exception) {
            return Helper\Render::error($exception);
        }
    }
}
