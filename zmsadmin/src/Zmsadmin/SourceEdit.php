<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Source as Entity;
use BO\Mellon\Validator;

class SourceEdit extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface  $request,
        \Psr\Http\Message\ResponseInterface $response,
        array                               $args
    )
    {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        if (!$workstation->hasSuperUseraccount()) {
            throw new Exception\NotAllowed();
        }

        if ('add' != $args['name']) {
            $source = \App::$http
                ->readGetResult('/source/' . $args['name'] . '/', ['resolveReferences' => 2])
                ->getEntity();
        }

        $parents = \App::$http->readGetResult('/source/dldb/', ['resolveReferences' => 2])->getEntity();
        $parentProviders = isset($parents->providers) ? $parents->providers : [];
        $parentRequests = isset($parents->requests) ? $parents->requests : [];

        $apiRes = \App::$http->readGetResult('/requestvariants/');
        $payload = json_decode((string)$apiRes->getResponse()->getBody(), true);
        try {
            $payload = json_decode((string)$apiRes->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $requestVariants = $payload['data'] ?? [];
        } catch (\JsonException $e) {
            \BO\Log::error('requestvariants JSON decode failed', ['error' => $e->getMessage()]);
            $requestVariants = [];
        }


        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $result = $this->testUpdateEntity($input);
            if ($result instanceof Entity) {
                return \BO\Slim\Render::redirect('sourceEdit', ['name' => $result->getSource()], [
                    'success' => 'source_saved'
                ]);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/sourceedit.twig',
            array(
                'title' => 'Mandanten bearbeiten',
                'menuActive' => 'source',
                'workstation' => $workstation,
                'source' => (isset($source)) ? $source : null,
                'parentProviders' => $parentProviders,
                'parentRequests' => $parentRequests,
                'requestVariants' => $requestVariants,
                'success' => $success,
                'exception' => (isset($result)) ? $result : null
            )
        );
    }

    protected function testUpdateEntity($input)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        try {
            $entity = \App::$http->readPostResult('/source/', $entity)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ('BO\Zmsentities\Exception\SchemaValidation' == $exception->template) {
                $exceptionData = [
                    'template' => 'exception/bo/zmsentities/exception/schemavalidation.twig'
                ];
                $exceptionData['data'] = $exception->data;
                return $exceptionData;
            } else {
                throw $exception;
            }
        }
        return $entity;
    }
}
