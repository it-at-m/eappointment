<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Ticketprinter as Entity;

class Ticketprinter
{
    public $entity;

    public function __construct($args, $request)
    {
        if (\array_key_exists('scopeId', $args)) {
            $scopeId = Validator::value($args['scopeId'])->isNumber()->getValue();
            $this->entity = static::createInstanceByScope($scopeId, $request);
        } else {
            $this->entity = static::createInstance($request);
        }
        $this->entity = \App::$http->readPostResult('/ticketprinter/', $this->entity)->getEntity();
    }


    protected static function createInstanceByScope($scopeId, $request)
    {
        $organisation = \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity();
        $entity = static::readWithHash($organisation, $request);
        $entity->buttonlist = 's'. $scopeId;
        $entity->toStructuredButtonList();
        return $entity;
    }

    protected static function createInstance($request)
    {
        $validator = $request->getAttribute('validator');
        $entity = new Entity($validator->getParameter('ticketprinter')->isArray()->getValue());
        $entity->toStructuredButtonList();
        foreach ($entity->buttons as $button) {
            if ('scope' == $button['type']) {
                $organisation = \App::$http->readGetResult(
                    '/organisation/scope/'. $button['scope']['id'] . '/'
                )->getEntity();
            } elseif ('cluster' == $button['type']) {
                $organisation = \App::$http->readGetResult(
                    '/organisation/cluster/'. $button['cluster']['id'] . '/'
                )->getEntity();
            }
            break;
        }

        if ($organisation->hasClusterScopesFromButtonList($entity->buttons)) {
            $ticketprinter = static::readWithHash($organisation, $request);
            $entity->hash = $ticketprinter->hash;
        }
        return $entity;
    }

    protected static function readWithHash(\BO\Zmsentities\Organisation $organisation, $request)
    {
        $cookies = $request->getCookieParams();
        $ticketprinterHash = \BO\Zmsclient\Ticketprinter::getHash();
        if (array_key_exists('Ticketprinter', $cookies) && ! $ticketprinterHash) {
            $ticketprinterHash = $cookies['Ticketprinter'];
        }

        if (!$ticketprinterHash) {
            $entity = \App::$http->readGetResult('/organisation/'. $organisation->id . '/hash/')->getEntity();
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
}
