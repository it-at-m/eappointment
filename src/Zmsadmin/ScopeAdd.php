<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;

/**
  * Handle requests concerning services
  *
  */
class ScopeAdd extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
    
        $input = $request->getParsedBody();
        if (array_key_exists('save', $input)) {
            try {
                $entity = new Entity($input);
                $scope = \App::$http->readPostResult(
                    '/scope/'. $entity->id .'/',
                    $entity
                )->getEntity();
                return Helper\Render::redirect(
                    'scope',
                    array(
                        'id' => $scope->id
                    ),
                    array(
                        'success' => 'scope_created'
                    )
                );
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return Helper\Render::checkedHtml(self::$errorHandler, $response, 'page/scope.twig', array(
            'title' => 'Standort',
            'action' => 'add',
            'menuActive' => 'owner'
        ));
    }
}
