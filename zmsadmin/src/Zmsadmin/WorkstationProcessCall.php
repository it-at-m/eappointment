<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Mellon\Validator;

class WorkstationProcessCall extends BaseController
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
        \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $validator = $request->getAttribute('validator');
        $isDirectCall = $validator->getParameter('direct')->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        if ($process->toProperty()->amendment->get() && ! $isDirectCall) {
            return \BO\Slim\Render::redirect(
                'workstationProcessPreCall',
                array(
                    'id' => $process->id
                )
            );
        }
        return \BO\Slim\Render::redirect(
            'workstationProcessCalled',
            array(
                'id' => $process->id
            )
        );
    }
}
