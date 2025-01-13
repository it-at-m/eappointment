<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;
use BO\Zmsentities\Schema\Schema;

class Department extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/department/' . $entityId . '/', ['resolveReferences' => 1])->getEntity();
        $organisation = \App::$http->readGetResult('/department/' . $entityId . '/organisation/')->getEntity();
        $input = $request->getParsedBody();

        if ($request->getMethod() === 'POST') {
            $input = $this->withCleanupLinks($input);
            $input = $this->withCleanupDayoffs($input);
            $input = $this->withEmailReminderDefaultValues($input);
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->id = $entityId;
            $entity->dayoff = $entity->getDayoffList()->withTimestampFromDateformat();
            $entity = \App::$http->readPostResult(
                '/department/' . $entity->id . '/',
                $entity
            )->getEntity();
            return \BO\Slim\Render::redirect('department', ['id' => $entityId], [
                'success' => 'department_saved'
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/department.twig',
            array(
                'title' => 'Standort',
                'workstation' => $workstation,
                'organisation' => $organisation,
                'department' => (new Schema($entity))->toSanitizedArray(),
                'hasAccess' => $entity->hasAccess($workstation->getUseraccount()),
                'menuActive' => 'owner',
                'success' => $success,
            )
        );
    }

    protected function withCleanupLinks(array $input)
    {
        if (!isset($input['links'])) {
            return $input;
        }
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['url'] == '');
        });

        foreach ($input['links'] as $index => $link) {
            $input['links'][$index]['target'] = (isset($link['target']) && $link['target']) ? 1 : 0;
        }

        return $input;
    }

    protected function withCleanupDayoffs(array $input)
    {
        if (!isset($input['dayoff'])) {
            return $input;
        }
        $dayoffs = $input['dayoff'];
        $input['dayoff'] = array_filter($dayoffs, function ($dayoff) {
            return !($dayoff['name'] === '');
        });

        return $input;
    }

    private function withEmailReminderDefaultValues(array $input)
    {
        if ($input['sendEmailReminderMinutesBefore'] === '') {
            $input['sendEmailReminderMinutesBefore'] = null;
        }

        if ($input['sendEmailReminderEnabled'] && empty($input['sendEmailReminderMinutesBefore'])) {
            $input['sendEmailReminderMinutesBefore'] = 120;
        }

        return $input;
    }
}
