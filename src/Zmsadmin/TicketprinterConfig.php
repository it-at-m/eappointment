<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

class TicketprinterConfig extends BaseController
{
    const SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';
    /**
     * @return String
     */
    public function __invoke(
        \psr\http\message\requestinterface $request,
        \psr\http\message\responseinterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scopeId = $workstation['scope']['id'];
        $entityId = Validator::value($scopeId)->isNumber()->getValue();

        $config = \App::$http->readGetResult('/config/', [], static::SECURE_TOKEN)->getEntity();

        $entity = \App::$http->readGetResult('/organisation/scope/'. $entityId .'/', [resolveReferences => 2])->getEntity();

        return \BO\Slim\Render::withHtml(
                $response,
                'page/ticketprinterConfig.twig',
                array(
                    'title' => 'Anmeldung an Warteschlange',
                    'config' => $config,
                    'organisation' => $entity->getArrayCopy(),
                    'menuActive' => 'ticketprinter'
                )
        );
    }
}
