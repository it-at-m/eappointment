<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class Workstation extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 2,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        if (! $workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        $validator = $request->getAttribute('validator');
        $selectedTime = $validator->getParameter('time')->isString()->getValue();
        $selectedTime = ($selectedTime) ? $selectedTime : null;
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $calledProcessId = $validator->getParameter('calledprocess')->isNumber()->getValue();
        $selectedScope = $validator->getParameter('selectedscope')->isNumber()->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstation.twig',
            array(
                'title' => 'Sachbearbeiter',
                'menuActive' => 'workstation',
                'workstation' => $workstation,
                'selectedDate' => $this->getSelectedDate($validator),
                'selectedTime' => $selectedTime,
                'selectedProcess' => $selectedProcessId,
                'selectedScope' => $selectedScope,
                'calledProcess' => $calledProcessId,
            )
        );
    }

    protected function getSelectedDate($validator)
    {
        $selectedDate = $validator->getParameter('date')->isString()->getValue();
        $selectedDateTime = $selectedDate ?
            (new \DateTimeImmutable($selectedDate))->setTime(\App::$now->format('H'), \App::$now->format('i')) :
            \App::$now;
        $selectedDate = ($selectedDateTime < \App::$now) ? \App::$now : $selectedDateTime;
        return $selectedDate->format('Y-m-d');
    }
}
