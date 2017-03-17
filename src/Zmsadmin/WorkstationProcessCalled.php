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
class WorkstationProcessCalled extends BaseController
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
        $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        $workstation->hasDepartmentList();

        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $authKey = Validator::value($args['authkey'])->isString()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/'. $authKey . '/')->getEntity();
        $process->scope['id'] = 999;
        $workstation->hasMatchingProcessScope($cluster, $process);

        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        if ($excludedIds) {
            $exclude = explode(',', $excludedIds);
        }
        $exclude[] = $processId;

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/called.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'menuActive' => 'workstation',
                'process' => $process,
                'exclude' => join(',', $exclude)
            )
        );
    }
}
