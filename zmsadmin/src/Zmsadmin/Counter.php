<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Counter extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedTime = $validator->getParameter('time')->isString()->getValue();
        $selectedTime = ($selectedTime) ? $selectedTime : null;
        $selectedProcess = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedScope = $validator->getParameter('selectedscope')->isNumber()->getValue();

        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/counter.twig',
            array(
                'title' => 'Tresen',
                'menuActive' => 'counter',
                'selectedDate' => $this->getSelectedDate($validator),
                'selectedTime' => $selectedTime,
                'selectedProcess' => $selectedProcess,
                'selectedScope' => $selectedScope,
                'workstation' => $workstation->getArrayCopy()            )
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
