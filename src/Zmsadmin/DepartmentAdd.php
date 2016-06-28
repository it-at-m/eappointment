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

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            try {
                $entity = new Entity($input);
                $department = \App::$http->readPostResult('/department/add/', $entity)
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

        return Helper\Render::checkedHtml(self::$errorHandler, $response, 'page/department.twig', array(
            'title' => 'Standort',
            'action' => 'add',
            'menuActive' => 'owner',
            'parentId' => Validator::value($args['parent_id'])->isNumber()->getValue()
        ));
    }
}
