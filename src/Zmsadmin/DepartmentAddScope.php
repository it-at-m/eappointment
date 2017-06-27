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
class DepartmentAddScope extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $providerAssigned = \App::$http->readGetResult(
            '/provider/dldb/',
            array(
                'isAssigned' => true
            )
        )->getCollection()->sortByName();

        $providerNotAssigned = \App::$http->readGetResult(
            '/provider/dldb/',
            array(
                'isAssigned' => false
            )
        )->getCollection()->sortByName();

        $departmentId = Validator::value($args['id'])->isNumber()->getValue();
        $department = \App::$http
            ->readGetResult('/department/'. $departmentId .'/', ['resolveReferences' => 2])->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $departmentId . '/organisation/')->getEntity();
        $input = $request->getParsedBody();

        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->hint = implode(' | ', $input['hint']);
            $entity = \App::$http->readPostResult('/department/'. $department->id .'/scope/', $entity)
                ->getEntity();
            (new Helper\FileUploader($request, 'uploadCallDisplayImage'))->writeUploadToScope($entity->id);
            return \BO\Slim\Render::redirect(
                'scope',
                array(
                    'id' => $entity->id
                ),
                array(
                    'success' => 'scope_created'
                )
            );
        }

        return \BO\Slim\Render::withHtml($response, 'page/scope.twig', array(
            'title' => 'Standort',
            'action' => 'add',
            'menuActive' => 'owner',
            'workstation' => $workstation,
            'organisation' => $organisation,
            'department' => $department,
            'providerList' => array(
                'notAssigned' => $providerNotAssigned,
                'assigned' => $providerAssigned
            )
        ));
    }
}
