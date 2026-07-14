<?php

namespace BO\Zmsbackend\Helper;

use BO\Slim\Render;
use BO\Zmsbackend\Useraccount\Service\Useraccount;

class TicketprinterAccess
{
    /**
     * Test if ticketprinter found and if protection is enabled
     *
     * @return array $useraccount
    */
    public static function testTicketprinter($entity)
    {
        if ($entity->hasId()) {
            $organisation = (new \BO\Zmsbackend\Organisation\Service\Organisation())->readByHash($entity->hash);
        }
        self::testTicketprinterNotFound($entity);
        self::testTicketprinterValidHash($entity);
        self::testMatchingClusterAndScopes($entity, $organisation->getId());
    }

    public static function testMatchingClusterAndScopes($entity, $organisationId)
    {
        if (isset($entity->buttons) && $entity->buttons) {
            $departmentList = (new \BO\Zmsbackend\Department\Service\Department())->readByOrganisationId($organisationId, 1);
            $scopeList = $departmentList->getUniqueScopeList();
            //$clusterList = $departmentList->getUniqueClusterList();
            foreach ($entity->buttons as $button) {
                if ('scope' == $button['type'] && ! $scopeList->hasEntity($button['scope']['id'])) {
                    throw new \BO\Zmsbackend\Ticketprinter\Exception\UnvalidButtonList();
                } /*elseif ('cluster' == $button['type'] && ! $clusterList->hasEntity($button['cluster']['id'])) {
                    throw new \BO\Zmsbackend\Ticketprinter\Exception\UnvalidButtonList();
                }*/
            }
        }
    }

    public static function testTicketprinterNotFound($entity)
    {
        if (! $entity->hasId() || ! (new \BO\Zmsbackend\Ticketprinter\Service\Ticketprinter())->readByHash($entity->getId())->hasId()) {
            throw new \BO\Zmsbackend\Ticketprinter\Exception\TicketprinterNotFound();
        }
    }

    public static function testTicketprinterIsProtectedEnabled($entity, $isProtectionEnabled)
    {
        if ($isProtectionEnabled && ! $entity->isEnabled()) {
            throw new \BO\Zmsbackend\Ticketprinter\Exception\TicketprinterNotEnabled();
        }
    }

    public static function testTicketprinterValidHash($entity)
    {
        if (
            isset($entity->id) &&
            $entity->id &&
            (new \BO\Zmsbackend\Ticketprinter\Service\Ticketprinter())->readByHash($entity->hash)->id != $entity->id
        ) {
            throw new \BO\Zmsbackend\Ticketprinter\Exception\TicketprinterHashNotValid();
        }
    }
}
