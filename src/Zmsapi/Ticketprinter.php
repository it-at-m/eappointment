<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsentities\Ticketprinter as Entity;

class Ticketprinter extends BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);
        $entity->testValid();
        $this->testTicketprinter($entity);

        if (! $entity->toProperty()->buttons->isAvailable()) {
            $entity = $entity->toStructuredButtonList();
        }

        $message = Response\Message::create($request);
        $message->data = (new Query)->readByButtonList($entity, \App::$now);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testTicketprinter($entity)
    {
        if (! (new Query)->readByHash($entity->hash)->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterNotFound();
        }
        if (! $entity->isEnabled()) {
            throw new Exception\Ticketprinter\TicketprinterNotEnabled();
        }
        if ((new Query)->readByHash($entity->hash)->id != $entity->id) {
            throw new Exception\Ticketprinter\TicketprinterHashNotValid();
        }
    }
}
