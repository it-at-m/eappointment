<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;

/**
 * Queue a process
 */
class ProcessQueueReset extends BaseController
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
        $validator = $request->getAttribute('validator');
        $processId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isNumber()->getValue();
        if ($processId) {
            $selectedProcess = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        }
        \App::$http->readPostResult('/process/status/queued/', $selectedProcess);

        return \BO\Slim\Render::redirect(
            'queue_table',
            array(
              'date' => $selectedDate
            ),
            array(
                'success' => 'process_reset_queued'
            )
        );
    }
}
