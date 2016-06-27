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

        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/department/'. $entityId .'/')->getEntity();

        if (!$entity->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            try {
                $entity = new Entity($input);
                $entity->id = $entityId;
                $entity = \App::$http->readPostResult(
                    '/department/'. $entity->id .'/',
                    $entity
                )->getEntity();
                self::$errorHandler->success = 'department_saved';
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/department.twig',
            array(
                'title' => 'Standort',
                'department' => $entity->getArrayCopy(),
                'menuActive' => 'owner'
            )
        );
    }
}
