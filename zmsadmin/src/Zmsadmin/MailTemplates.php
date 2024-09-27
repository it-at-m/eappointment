<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Config as Entity;

class MailTemplates extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $providerId = $workstation->scope['provider']['id'];
        $config = \App::$http->readGetResult('/config/')->getEntity();

        $mergedMailTemplates = \App::$http->readGetResult('/merged-mailtemplates/'.$providerId.'/')->getCollection();
        forEach($mergedMailTemplates as $template) {
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

        $mainProcessExample = ((new \BO\Zmsentities\Process)->getExample());
        $mainProcessExample->id = 987654;
        $dateTime = new \DateTimeImmutable("2015-10-23 08:00:00", new \DateTimeZone('Europe/Berlin'));
        $mainProcessExample->getFirstAppointment()->setDateTime($dateTime);
        $mainProcessExample->requests[] = (new \BO\Zmsentities\Request())->getExample();

        $processExample = ((new \BO\Zmsentities\Process)->getExample());
        $processExample->scope = ((new \BO\Zmsentities\Scope)->getExample());
        $processExample2 = clone $processExample;
        $dateTime = new \DateTimeImmutable("2015-12-30 11:55:00", new \DateTimeZone('Europe/Berlin'));
        $processExample2->getFirstAppointment()->setDateTime($dateTime);

        $processListExample = new \BO\Zmsentities\Collection\ProcessList();
        $processListExample->addEntity($processExample);
        $processListExample->addEntity($processExample2);
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/mailtemplates.twig',
            array(
                'title' => 'Konfiguration System',
                'providerId' => $providerId,
                'workstation' => $workstation,
                'config' => $config,
                'mailtemplates' => $mergedMailTemplates,
                'processExample' => $mainProcessExample,
                'processListExample' => $processListExample,
                'menuActive' => 'mailtemplates',
                'success' => $success
            )
        );
    }
}
