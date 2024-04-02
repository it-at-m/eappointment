<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

use BO\Zmsentities\Ticketprinter as Entity;
use BO\Zmsentities\Organisation;
use BO\Zmsclient\Ticketprinter as TicketprinterClient;
use BO\Zmsticketprinter\Exception\OrganisationNotFound as OrganisationNotFoundException;
use Psr\Http\Message\RequestInterface;

class Ticketprinter
{
    protected $entity = null;

    protected $scopeId = null;

    protected $organisation = null;

    protected $requestParams = [];

    public function __construct($args, RequestInterface $request)
    {
        $this->setRequestParameters($request);
        $this->scopeId = $this->setScopeId($args, $request);
        $this->organisation = $this->readOrganisation();
        $entity = $this->getAssembledEntity();

        $hash = static::getHashFromRequest($request);
        if ('' === $hash) {
            $entity = $this->writeNewWithHash($request, $entity);
        } else {
            $entity = $this->getByHash($hash, $entity);
        }

        $this->entity = \App::$http->readPostResult('/ticketprinter/', $entity)->getEntity();
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function getScopeId(): int
    {
        return $this->scopeId;
    }

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    protected function setScopeId(array $args, RequestInterface $request)
    {
        $validator = $request->getAttribute('validator');
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        if (isset($args['scopeId'])) {
            $scopeId = $validator::value($args['scopeId'])->isNumber()->getValue();
        }
        return $scopeId;
    }

    public static function getHashFromRequest(RequestInterface $request): string
    {
        $cookies = $request->getCookieParams();
        $hash = TicketprinterClient::getHash();
        if (array_key_exists(TicketprinterClient::HASH_COOKIE_NAME, $cookies) && ! $hash) {
            $hash = $cookies[TicketprinterClient::HASH_COOKIE_NAME];
        }
        return $hash;
    }

    protected function getByHash(string $hash, Entity $entity): Entity
    {
        $entityWithHash = \App::$http->readGetResult('/ticketprinter/'. $hash . '/')->getEntity();
        $entity->hash = $entityWithHash->hash;
        $entity->enabled = $entityWithHash->enabled;
        return $entity;
    }

    protected function writeNewWithHash(RequestInterface $request, Entity $entity): Entity
    {
        if (null === $this->organisation) {
            throw new OrganisationNotFoundException();
        }
        $entityWithHash = \App::$http->readGetResult(
            '/organisation/'. $this->organisation->getId() . '/hash/',
            ['name' => (isset($this->requestParams['name'])) ? $this->requestParams['name'] : '']
        )->getEntity();
        TicketprinterClient::setHash($entityWithHash->hash, $request);
        $entity->hash = $entityWithHash->hash;
        $entity->enabled = $entityWithHash->enabled;
        return $entity;
    }

    protected function setRequestParameters(RequestInterface $request): void
    {
        $validator = $request->getAttribute('validator');
        $this->requestParams = $validator->getParameter('ticketprinter')->isArray()->getValue();
    }

    protected function getAssembledEntity(): Entity
    {
        $entity = new Entity($this->requestParams);
        if ($this->scopeId) {
            $entity = new Entity();
            $entity->buttonlist = 's'. $this->scopeId;
        }
        $entity = $entity->toStructuredButtonList();
        return $entity;
    }

    protected function readOrganisation(): Organisation
    {
        $organisation = null;
        $ticketprinter = $this->getAssembledEntity();
        if ($this->scopeId) {
            $organisation = \App::$http->readGetResult(
                '/scope/'. $this->scopeId . '/organisation/',
                ['resolveReferences' => 2]
            )->getEntity();
        }
        $nextButton = array_shift($ticketprinter->buttons);
        while (! $organisation && $nextButton) {
            if (in_array($nextButton['type'], ['scope', 'request'])) {
                $organisation = \App::$http->readGetResult(
                    '/scope/'. $nextButton['scope']['id'] . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity();
            }
            $nextButton = array_shift($ticketprinter->buttons);
        }
        return $organisation;
    }
}
