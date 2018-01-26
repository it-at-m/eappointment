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
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
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
            $result = $this->testUpdateEntity($input, $entityId);
            if ($result instanceof Entity) {
                $this->writeUploadedImage($request, $entityId, $input);
                return \BO\Slim\Render::redirect('scope', ['id' => $entityId], [
                    'success' => 'scope_saved'
                ]);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/scope.twig',
            array(
                'title' => 'Standort',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'scope' => $entity,
                'organisation' => $organisation,
                'department' => $department,
                'providerList' => array(
                    'notAssigned' => $providerNotAssigned,
                    'assigned' => $providerAssigned
                ),
                'callDisplayImage' => $callDisplayImage,
                'success' => $success,
                'exception' => (isset($result)) ? $result : null
            )
        );
    }

    protected function testUpdateEntity($input, $entityId)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        $entity->id = $entityId;
        try {
            $entity = \App::$http->readPostResult('/scope/' . $entity->id . '/', $entity)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ('' != $exception->template) {
                return [
                  'template' => strtolower($exception->template),
                  'data' => $exception->data
                ];
            }
            throw $exception;
        }
        return $entity;
    }

    protected function writeUploadedImage(\Psr\Http\Message\RequestInterface $request, $entityId, $input)
    {
        if (isset($input['removeImage']) && $input['removeImage']) {
            \App::$http->readDeleteResult('/scope/'. $entityId .'/imagedata/calldisplay/');
        } else {
            (new Helper\FileUploader($request, 'uploadCallDisplayImage'))->writeUploadToScope($entityId);
        }
    }
}
