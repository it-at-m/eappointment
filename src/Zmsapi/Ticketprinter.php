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

/**
 *
 * @SuppressWarnings(Coupling)
 *
 */
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
        $this->testMatchingClusterAndScopes($entity);

        $message = Response\Message::create($request);
        $message->data = (new Query)->readByButtonList($entity, \App::$now);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testMatchingClusterAndScopes($entity)
    {
        $organisation = (new \BO\Zmsdb\Organisation)->readByHash($entity->hash);
        $departmentList = (new \BO\Zmsdb\Department)->readByOrganisationId($organisation->id, 1);
        $scopeList = $departmentList->getUniqueScopeList();
        $clusterList = $departmentList->getUniqueClusterList();
        foreach ($entity->buttons as $button) {
            if ('scope' == $button['type'] && ! $scopeList->hasEntity($button['scope']['id'])) {
                throw new Exception\Ticketprinter\UnvalidButtonList();
            } elseif ('cluster' == $button['type'] && ! $clusterList->hasEntity($button['cluster']['id'])) {
                throw new Exception\Ticketprinter\UnvalidButtonList();
            }
        }
    }

    protected function testTicketprinter($entity)
    {
        if (! (new Query)->readByHash($entity->hash)->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterNotFound();
        }
        if (! $entity->isEnabled()) {
            throw new Exception\Ticketprinter\TicketprinterNotEnabled();
        }
        if ($entity->id && (new Query)->readByHash($entity->hash)->id != $entity->id) {
            throw new Exception\Ticketprinter\TicketprinterHashNotValid();
        }
    }
}
