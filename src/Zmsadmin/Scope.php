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
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
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

        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/scope/' . $entityId . '/')->getEntity();

        $callDisplayImage = \App::$http->readGetResult('/scope/'. $entityId .'/imagedata/calldisplay/')->getEntity();
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->hint = implode(' | ', $input['hint']);
            $entity->id = $entityId;
            $entity = \App::$http->readPostResult('/scope/' . $entity->id . '/', $entity)->getEntity();
            $uploadedImage = (new Helper\FileUploader($request, $entityId))->getEntity();
            if ($uploadedImage) {
                $callDisplayImage = $uploadedImage;
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/scope.twig',
            array(
                'title' => 'Standort',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'scope' => $entity->getArrayCopy(),
                'providerList' => array(
                    'notAssigned' => $providerNotAssigned,
                    'assigned' => $providerAssigned
                ),
                'callDisplayImage' => $callDisplayImage
            )
        );
    }
}
