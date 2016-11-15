<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

class Ticketprinter
{
    public $entity;

    public function __construct($args, $request)
    {
        if (\array_key_exists('scope', $args)) {
            $this->entity = static::createInstanceByScope($args['scope'], $request);
        } else {
            $this->entity = static::createInstance();
        }
        $this->entity = \App::$http->readPostResult('/ticketprinter/', $this->entity)->getEntity();
    }


    protected static function createInstanceByScope($scopeId, $request)
    {
        $organisation = \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity();
        $entity = static::readWithHash($organisation, $request);
        $entity->buttonlist = 's'. $scopeId;
        return $entity;
    }

    protected static function createInstance()
    {
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
