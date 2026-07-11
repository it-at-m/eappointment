<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Owner\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;

class OwnerAddOrganisation extends \BO\Zmsbackend\Api\BaseController
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
        $user = new \BO\Zmsbackend\Helper\User($request, 2);
        $user->checkPermissions('organisation');
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $owner = (new \BO\Zmsbackend\Owner\Service\Owner())->readEntity($args['id'], 2);
        $user->checkPermissions(

            new \BO\Zmsentities\Useraccount\EntityAccess($owner)
        );
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $organisation = new \BO\Zmsentities\Organisation($input);
        $organisation->testValid();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new Query())->writeEntity($organisation, $args['id']);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
