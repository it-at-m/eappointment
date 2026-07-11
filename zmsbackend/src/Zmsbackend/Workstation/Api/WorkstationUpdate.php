<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Workstation\Service\Workstation;

class WorkstationUpdate extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $currentWorkstation = (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Workstation($input);
        $entity->testValid();
        \BO\Zmsbackend\Helper\User::testWorkstationIsOveraged($entity);
        if ($entity->getUseraccount()->id != $currentWorkstation->getUseraccount()->id) {
            throw new \BO\Zmsbackend\Workstation\Exception\WorkstationAccessFailed();
        }
        $entity->getUseraccount()->rights = $currentWorkstation->getUseraccount()->rights;
        $workstation = (new \BO\Zmsbackend\Workstation\Service\Workstation())->updateEntity($entity, $resolveReferences);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);

        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
