<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class WorkstationProcessPreCall extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');

        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        $workstation->hasDepartmentList();

        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $authKey = Validator::value($args['authkey'])->isString()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/'. $authKey . '/')->getEntity();

        $exlude = explode(',', $validator->getParameter('exclude')->isString()->getValue());
        $exclude = ($exclude) ? array_push($processId, $exclude) : array($processId);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/preCall.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'menuActive' => 'workstation',
                'process' => $process,
                'exclude' => implode(',', $exlude)
            )
        );
    }
}
