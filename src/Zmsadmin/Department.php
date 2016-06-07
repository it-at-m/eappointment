<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;

/**
  * Handle requests concerning services
  *
  */
class Department extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $department = \App::$http->readGetResult(
            '/department/'. $args['id'] .'/'
        )->getEntity();

        if (!isset($department['id'])) {
            return \BO\Slim\Render::withError($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', $input)) {
            try {
                $entity = new Entity($input);
                $entity->id = $args['id'];
                $department = \App::$http->readPostResult(
                    '/department/'. $entity->id .'/',
                    $entity
                )->getEntity();
                self::$errorHandler->success = 'department_saved';
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }
        return Helper\Render::checkedHtml(self::$errorHandler, $response, 'page/department.twig', array(
            'title' => 'Standort',
            'department' => $department->getArrayCopy(),
            'menuActive' => 'owner'
        ));
    }
}
