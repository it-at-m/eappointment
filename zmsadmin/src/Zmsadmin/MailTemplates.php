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

        $mailtemplates = \App::$http->readGetResult('/mailtemplates/')->getCollection();
        $customMailtemplates = \App::$http->readGetResult('/custom-mailtemplates/'.$providerId.'/')->getCollection();
        $mergedMailTemplates = \App::$http->readGetResult('/merged-mailtemplates/'.$providerId.'/')->getCollection();
        forEach($mergedMailTemplates as $template) {
            if ($template['provider']) {
                $template->isCustom = true;
            }
        }

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

        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $entity = clone $config;
            $entity->setPreference($input['key'], $input['property'], $input['value']);
            $entity = \App::$http->readPostResult(
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


    function mergeMailTemplates($generalTemplates, $customTemplates) {
        $customTemplatesByName = [];

        if ($customTemplates) {
            foreach ($customTemplates as $template) {
                $template['isCustom'] = true; // Add isCustom property to custom templates
                $customTemplatesByName[$template['name']] = $template;
            }
        }

        $mergedTemplates = [];

        if ($generalTemplates) {
            foreach ($generalTemplates as $template) {
                if (isset($customTemplatesByName[$template['name']])) {
                    $mergedTemplates[] = $customTemplatesByName[$template['name']];
                } else {
                    $mergedTemplates[] = $template;
                }
            }
        }

        return $mergedTemplates;
    }

}
