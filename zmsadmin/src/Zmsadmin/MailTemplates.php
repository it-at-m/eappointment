<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Request;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class MailTemplates extends BaseController
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
        if (!$workstation->getUseraccount()->hasPermissions(['mailtemplates'])) {
            throw new UserAccountMissingRights();
        }
        $providerId = $workstation->scope['provider']['id'];

        $scopeName = $workstation->scope['contact']['name'];
        $scopeId = $workstation->scope['id'];

        if (isset($args['scopeId']) && !empty($args['scopeId'])) {
            $scope = \App::$http
                ->readGetResult('/scope/' . $args['scopeId'] . '/', ['resolveReferences' => 1])
                ->getEntity();

            $scopeName = $scope->contact->name;
            $scopeId = $scope->id;
            $providerId = $scope->provider->id;
        }

        $config = \App::$http->readGetResult('/config/')->getEntity();

        $mergedMailTemplates = \App::$http->readGetResult('/merged-mailtemplates/' . $providerId . '/')->getCollection();
        foreach ($mergedMailTemplates as $template) {
            if ($template['provider']) {
                $template->isCustom = true;
            }
        }

        $priorityNames = [
            'mail_preconfirmed.twig',
            'mail_confirmation.twig',
            'mail_reminder.twig',
            'mail_delete.twig'
        ];

        $mergedMailTemplates->prioritizeByName($priorityNames);

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

        return Render::withHtml(
            $response,
            'page/mailtemplates.twig',
            array(
                'title' => 'Mail Templates',
                'pageTitle' => 'Mail Templates für ' . $scopeName,
                'providerId' => $providerId,
                'workstation' => $workstation,
                'config' => $config,
                'scopeId' => $scopeId,
                'mailtemplates' => $mergedMailTemplates,
                'processExample' => $mainProcessExample,
                'processListExample' => $processListExample,
                'menuActive' => 'mailtemplates',
                'success' => $success
            )
        );
    }
}
