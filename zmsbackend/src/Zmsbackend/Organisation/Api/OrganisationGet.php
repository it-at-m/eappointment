<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;

class OrganisationGet extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = new \BO\Zmsbackend\Helper\User($request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $organisation = (new Query())->readEntity($args['id'], $resolveReferences);
        if (! $organisation) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);

        if ($workstation->hasRights()) {
            $workstation->checkRights('department');
        } else {
            $organisation = $organisation->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
