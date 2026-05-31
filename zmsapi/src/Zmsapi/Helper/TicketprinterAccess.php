<?php

namespace BO\Zmsapi\Helper;

use BO\Slim\Render;
use BO\Zmsdb\Useraccount;

class TicketprinterAccess
{
    /**
     * Test if ticketprinter found and if protection is enabled
     */
    public static function testTicketprinter(\BO\Zmsdb\Resource $entity): void
    {
        if ($entity->hasId()) {
            $organisation = (new \BO\Zmsdb\Organisation())->readByHash($entity->hash);
        }
        self::testTicketprinterNotFound($entity);
        self::testTicketprinterValidHash($entity);
        self::testMatchingClusterAndScopes($entity, $organisation->getId());
    }

    /**
     * @return void
     */
    public static function testMatchingClusterAndScopes($entity, $organisationId)
    {
        if (isset($entity->buttons) && $entity->buttons) {
            $departmentList = (new \BO\Zmsdb\Department())->readByOrganisationId($organisationId, 1);
            $scopeList = $departmentList->getUniqueScopeList();
            //$clusterList = $departmentList->getUniqueClusterList();
            foreach ($entity->buttons as $button) {
                if ('scope' == $button['type'] && ! $scopeList->hasEntity($button['scope']['id'])) {
                    throw new \BO\Zmsapi\Exception\Ticketprinter\UnvalidButtonList();
                } /*elseif ('cluster' == $button['type'] && ! $clusterList->hasEntity($button['cluster']['id'])) {
                    throw new \BO\Zmsapi\Exception\Ticketprinter\UnvalidButtonList();
                }*/
            }
        }
    }

    /**
     * @return void
     */
    public static function testTicketprinterNotFound($entity)
    {
        if (! $entity->hasId() || ! (new \BO\Zmsdb\Ticketprinter())->readByHash($entity->getId())->hasId()) {
            throw new \BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotFound();
        }
    }

    /**
     * @return void
     */
    public static function testTicketprinterIsProtectedEnabled($entity, $isProtectionEnabled)
    {
        if ($isProtectionEnabled && ! $entity->isEnabled()) {
            throw new \BO\Zmsapi\Exception\Ticketprinter\TicketprinterNotEnabled();
        }
    }

    /**
     * @return void
     */
    public static function testTicketprinterValidHash($entity)
    {
        if (
            isset($entity->id) &&
            $entity->id &&
            (new \BO\Zmsdb\Ticketprinter())->readByHash($entity->hash)->id != $entity->id
        ) {
            throw new \BO\Zmsapi\Exception\Ticketprinter\TicketprinterHashNotValid();
        }
    }
}
