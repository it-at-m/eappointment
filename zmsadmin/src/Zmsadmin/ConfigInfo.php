<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Request;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class ConfigInfo extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['config'])) {
            throw new UserAccountMissingRights();
        }
        $config = \App::$http->readGetResult('/config/')->getEntity();

        $mailtemplates = \App::$http->readGetResult('/mailtemplates/')->getCollection();

        $mainProcessExample = ((new Process())->getExample());
        $mainProcessExample->id = 987654;
        $dateTime = new \DateTimeImmutable("2015-10-23 08:00:00", new \DateTimeZone('Europe/Berlin'));
        $mainProcessExample->getFirstAppointment()->setDateTime($dateTime);
        $mainProcessExample->requests[] = (new Request())->getExample();

        $processExample = ((new Process())->getExample());
        $processExample->scope = ((new Scope())->getExample());
        $processExample2 = clone $processExample;
        $dateTime = new \DateTimeImmutable("2015-12-30 11:55:00", new \DateTimeZone('Europe/Berlin'));
        $processExample2->getFirstAppointment()->setDateTime($dateTime);

        $processListExample = new ProcessList();
        $processListExample->addEntity($processExample);
        $processListExample->addEntity($processExample2);
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();

        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $entity = clone $config;
            $entity->setPreference($input['key'], $input['property'], $input['value']);
            \App::$http->readPostResult(
                '/config/',
                $entity
            )->getEntity();
            return \BO\Slim\Render::redirect(
                'configinfo',
                array(
                    'title' => 'Konfiguration System',
                    'workstation' => $workstation,
                    'config' => $config,
                    'processExample' => $mainProcessExample,
                    'processListExample' => $processListExample,
                    'menuActive' => 'configinfo'
                ),
                array(
                    'success' => 'config_saved'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/configinfo.twig',
            array(
                'title' => 'Konfiguration System',
                'workstation' => $workstation,
                'config' => $config,
                'mailtemplates' => $mailtemplates,
                'processExample' => $mainProcessExample,
                'processListExample' => $processListExample,
                'menuActive' => 'configinfo',
                'success' => $success
            )
        );
    }
}
