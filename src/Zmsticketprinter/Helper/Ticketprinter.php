<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Ticketprinter as Entity;

class Ticketprinter
{
    public $entity;
    public static $organisation = null;

    public function __construct($args, $request)
    {
        if (\array_key_exists('scopeId', $args)) {
            $this->entity = static::createInstanceByScope($args, $request);
        } else {
            $this->entity = static::createInstance($request);
        }
        $this->entity = \App::$http->readPostResult('/ticketprinter/', $this->entity)->getEntity();
    }

    public static function readWithHash($request)
    {
        $validator = $request->getAttribute('validator');
        $parameters = $validator->getParameter('ticketprinter')->isArray()->getValue();
        $cookies = $request->getCookieParams();

        $ticketprinterHash = \BO\Zmsclient\Ticketprinter::getHash();
        if (array_key_exists('Ticketprinter', $cookies) && ! $ticketprinterHash) {
            $ticketprinterHash = $cookies['Ticketprinter'];
        }
        if (!$ticketprinterHash) {
            $entity = \App::$http->readGetResult(
                '/organisation/'. self::$organisation->id . '/hash/',
                ['name' => $parameters['name']]
            )->getEntity();
            \BO\Zmsclient\Ticketprinter::setHash($entity->hash);
        } else {
            $entity = \App::$http->readGetResult('/ticketprinter/'. $ticketprinterHash . '/')->getEntity();
        }
        return $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    protected static function createInstanceByScope($args, $request)
    {
        $scopeId = Validator::value($args['scopeId'])->isNumber()->getValue();
        $entity = new Entity();
        $entity->buttonlist = 's'. $scopeId;
        $entity = $entity->toStructuredButtonList();
        self::$organisation = self::readOrganisation($entity, $scopeId);
        $ticketprinter = static::readWithHash($request);
        $entity->hash = $ticketprinter->hash;
        $entity->enabled = $ticketprinter->enabled;
        return $entity;
    }

    protected static function createInstance($request)
    {
        $validator = $request->getAttribute('validator');
        $entity = new Entity($validator->getParameter('ticketprinter')->isArray()->getValue());
        $entity = $entity->toStructuredButtonList();
        self::$organisation = self::readOrganisation($entity);
        $ticketprinter = static::readWithHash($request);
        $entity->hash = $ticketprinter->hash;
        $entity->enabled = $ticketprinter->enabled;
        return $entity;
    }

    protected static function readOrganisation($entity, $scopeId = false)
    {
        $organisation = null;
        $ticketprinter = clone $entity;
        if ($scopeId) {
            $organisation = \App::$http->readGetResult(
                '/scope/'. $scopeId . '/organisation/',
                ['resolveReferences' => 2]
            )->getEntity();
        }
        $nextButton = array_shift($ticketprinter->buttons);
        while (! $organisation && $nextButton) {
            if ('scope' == $nextButton['type']) {
                $organisation = \App::$http->readGetResult(
                    '/scope/'. $nextButton['scope']['id'] . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity();
            }
            /* cluster not supported anymore
            elseif ('cluster' == $nextButton['type']) {
                $organisation = \App::$http->readGetResult(
                    '/cluster/'. $nextButton['cluster']['id'] . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity();
            }
            */
            $nextButton = array_shift($ticketprinter->buttons);
        }
        return $organisation;
    }
}
