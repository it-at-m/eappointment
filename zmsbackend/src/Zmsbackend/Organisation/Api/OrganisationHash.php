<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Organisation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Ticketprinter\Service\Ticketprinter as Ticketprinter;
use BO\Zmsbackend\Organisation\Service\Organisation as Query;

class OrganisationHash extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $organisation = (new Query())->readEntity($args['id']);
        if (! $organisation) {
            throw new \BO\Zmsbackend\Organisation\Exception\OrganisationNotFound();
        }
        //$ticketprinterName = Validator::param('name')->isString()->setDefault('')->getValue();
        $ticketprinterName = "Ticket Printer for " . $organisation->name;
        $ticketprinter = (new \BO\Zmsbackend\Ticketprinter\Service\Ticketprinter())->writeEntityWithHash($organisation->id, $ticketprinterName);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $ticketprinter;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
