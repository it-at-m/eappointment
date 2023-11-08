<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Ticketprinter as Ticketprinter;
use \BO\Zmsdb\Organisation as Query;

class OrganisationHash extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $ticketprinterName = Validator::param('name')->isString()->setDefault('')->getValue();
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $organisation = (new Query())->readEntity($args['id']);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }
        $ticketprinter = (new Ticketprinter())->writeEntityWithHash($organisation->id, $ticketprinterName);

        $message = Response\Message::create($request);
        $message->data = $ticketprinter;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
