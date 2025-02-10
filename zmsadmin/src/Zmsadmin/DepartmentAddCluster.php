<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Cluster as Entity;
use BO\Mellon\Validator;

class DepartmentAddCluster extends BaseController
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
        $departmentId = Validator::value($args['departmentId'])->isNumber()->getValue();
        $department = \App::$http
            ->readGetResult('/department/' . $departmentId . '/', ['resolveReferences' => 2])->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $departmentId . '/organisation/')->getEntity();
        $input = $request->getParsedBody();

        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->scopes = (new \BO\Zmsentities\Collection\ScopeList($entity->scopes))->withUniqueScopes();
            $entity = \App::$http
                ->readPostResult('/department/' . $department->id . '/cluster/', $entity)
                ->getEntity();
            (new Helper\FileUploader($request, 'uploadCallDisplayImage'))->writeUploadToCluster($entity->id);
            return \BO\Slim\Render::redirect(
                'cluster',
                array(
                    'departmentId' => $department->id,
                    'clusterId' => $entity->id
                ),
                array(
                    'success' => 'cluster_created'
                )
            );
        }

        return \BO\Slim\Render::withHtml($response, 'page/cluster.twig', array(
            'title' => 'Cluster',
            'action' => 'add',
            'menuActive' => 'owner',
            'workstation' => $workstation,
            'scopeList' => $department->getScopeList()->withUniqueScopes(),
            'organisation' => $organisation,
            'department' => $department
        ));
    }
}
