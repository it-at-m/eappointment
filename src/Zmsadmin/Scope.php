<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;
use BO\Mellon\Validator;

/**
 * Handle requests concerning services
 */
class Scope extends BaseController
{

    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $confirm_success = $request->getAttribute('validator')->getParameter('confirm_success')->isString()->getValue();
        $providerAssigned = \App::$http->readGetResult(
            '/provider/dldb/',
            array(
                'isAssigned' => true
            )
        )->getCollection()->withUniqueProvider()->sortByName();

        $providerNotAssigned = \App::$http->readGetResult(
            '/provider/dldb/',
            array(
                'isAssigned' => false
            )
        )->getCollection()->withUniqueProvider()->sortByName();

        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/scope/' . $entityId . '/', ['resolveReferences' => 1])->getEntity();
        $organisation = \App::$http->readGetResult('/scope/' . $entityId . '/organisation/')->getEntity();
        $department = \App::$http->readGetResult('/scope/' . $entityId . '/department/')->getEntity();
        $callDisplayImage = \App::$http->readGetResult('/scope/'. $entityId .'/imagedata/calldisplay/')->getEntity();
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->hint = implode(' | ', $input['hint']);
            $entity->id = $entityId;
            $entity = \App::$http->readPostResult('/scope/' . $entity->id . '/', $entity)->getEntity();
            (new Helper\FileUploader($request, 'uploadCallDisplayImage'))->writeUploadToScope($entityId);
            return \BO\Slim\Render::redirect('scope', ['id' => $entityId], [
                'confirm_success' => \App::$now->getTimeStamp()
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/scope.twig',
            array(
                'title' => 'Standort',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'scope' => $entity->getArrayCopy(),
                'organisation' => $organisation,
                'department' => $department,
                'providerList' => array(
                    'notAssigned' => $providerNotAssigned,
                    'assigned' => $providerAssigned
                ),
                'callDisplayImage' => $callDisplayImage,
                'confirm_success' => $confirm_success,
            )
        );
    }
}
