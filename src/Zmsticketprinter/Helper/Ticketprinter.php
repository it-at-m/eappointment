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

    public function __construct($args)
    {
        if (\array_key_exists('scope', $args)) {
            $this->entity = static::createInstanceByScope($args['scope']);
        } else {
            $this->entity = static::createInstance();
        }
        $this->entity = \App::$http->readPostResult('/ticketprinter/', $this->entity)->getEntity();
    }


    protected static function createInstanceByScope($scopeId)
    {
        $organisation = \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity();
        $ticketprinterHash = \BO\Zmsclient\Ticketprinter::getHash();
        if (!$ticketprinterHash) {
            $entity = \App::$http->readGetResult('/organisation/'. $organisation->id . '/hash/')->getEntity();
        } else {
            $entity = \App::$http->readGetResult('/ticketprinter/'. $ticketprinterHash . '/')->getEntity();
        }
        $entity->buttonlist = 's'. $scopeId;
        return $entity;
    }

    protected static function createInstance()
    {
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
