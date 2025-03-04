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
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();

        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();

        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http
            ->readGetResult('/scope/' . $entityId . '/', [
                'resolveReferences' => 1,
                'accessRights' => 'scope',
                'gql' => Helper\GraphDefaults::getScope()
            ])
            ->getEntity();

        $sourceList = $this->readSourceList();
        $providerList = Helper\ProviderHandler::readProviderList($entity->getSource());
        $currentSource = $this->readCurrentSource($entity->getSource());

        $organisation = \App::$http->readGetResult('/scope/' . $entityId . '/organisation/')->getEntity();
        $department = \App::$http->readGetResult('/scope/' . $entityId . '/department/')->getEntity();
        $callDisplayImage = \App::$http->readGetResult('/scope/' . $entityId . '/imagedata/calldisplay/')->getEntity();
        $input = $request->getParsedBody();
        if ($request->getMethod() === 'POST') {
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
                'provider' => $entity->provider,
                'organisation' => $organisation,
                'department' => $department,
                'providerList' => $providerList,
                'sourceList' => $sourceList,
                'source' => $currentSource,
                'callDisplayImage' => $callDisplayImage,
                'success' => $success,
                'exception' => (isset($result)) ? $result : null
            )
        );
    }

    protected function readSourceList()
    {
        $sourceList = \App::$http->readGetResult('/source/')->getCollection();
        return $sourceList;
    }

    protected function readCurrentSource($source)
    {
        $source = \App::$http->readGetResult('/source/' . $source . '/')->getEntity();
        return $source;
    }

    /**
     * @param \BO\Zmsentities\Scope $input scope entity, if used without ID, a new scope is created
     * @param Number $entityId Might be the entity scope or department if called from DepartmentAddScope
     */
    protected function testUpdateEntity($input, $entityId = null)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        try {
            if ($entity->id) {
                $entity->id = $entityId;
                $entity = \App::$http->readPostResult('/scope/' . $entity->id . '/', $entity)->getEntity();
            } else {
                $entity = \App::$http->readPostResult('/department/' . $entityId . '/scope/', $entity)
                                     ->getEntity();
            }
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if (
                '' != $exception->template
                && \App::$slim->getContainer()->get('view')->getLoader()->exists($template)
            ) {
                return [
                    'template' => $template,
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
            \App::$http->readDeleteResult('/scope/' . $entityId . '/imagedata/calldisplay/');
        } else {
            (new Helper\FileUploader($request, 'uploadCallDisplayImage'))->writeUploadToScope($entityId);
        }
    }
}
