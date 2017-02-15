<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Cluster as Entity;
use BO\Mellon\Validator;

class Cluster extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $entityId = Validator::value($args['clusterId'])->isNumber()->getValue();
        $departmentId = Validator::value($args['departmentId'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/cluster/' . $entityId . '/', ['resolveReferences' => 2])->getEntity();

        $department = \App::$http->readGetResult(
            '/department/' . $departmentId . '/',
            ['resolveReferences' => 2]
        )->getEntity();

        $callDisplayImage = \App::$http->readGetResult('/cluster/'. $entityId .'/imagedata/calldisplay/')->getEntity();
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->id = $entityId;
            $entity = \App::$http->readPostResult('/cluster/' . $entity->id . '/', $entity)->getEntity();
            $uploadedImage = (new Helper\FileUploader($request, $entityId, 'cluster'))->getEntity();
            if ($uploadedImage) {
                $callDisplayImage = $uploadedImage;
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/cluster.twig',
            array(
                'title' => 'Cluster',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'cluster' => $entity->getArrayCopy(),
                'department' => $department,
                'scopeList' => $department->getScopeList()->sortByContactName(),
                'callDisplayImage' => $callDisplayImage
            )
        );
    }
}
