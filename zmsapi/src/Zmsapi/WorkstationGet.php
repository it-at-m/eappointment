<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

 namespace BO\Zmsapi;

 use BO\Slim\Render;
 use BO\Mellon\Validator;

class WorkstationGet extends BaseController
{
    /**
     * @SuppressWarnings (Param)
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $workstation = (new Helper\User($request, $resolveReferences))->checkPermissions();

        // Check if the password field exists and remove it from the response
        if (isset($workstation['useraccount']['password'])) {
            unset($workstation['useraccount']['password']);
        }

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
