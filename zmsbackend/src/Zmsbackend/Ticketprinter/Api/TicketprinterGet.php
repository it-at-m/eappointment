<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Ticketprinter\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Ticketprinter\Service\Ticketprinter as Query;

class TicketprinterGet extends \BO\Zmsbackend\Api\BaseController
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
        $ticketprinter = (new Query())->readByHash($args['hash']);
        \BO\Zmsbackend\Helper\TicketprinterAccess::testTicketprinter($ticketprinter);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $ticketprinter;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
