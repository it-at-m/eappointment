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

        $process = new \BO\Zmsentities\Process();
        $process->id = Validator::value($args['id'])->isNumber()->getValue();
        $workstation->process = ($workstation->hasProcessCalled()) ? $workstation->process : $process;
        $workstation = \App::$http->readPostResult('/workstation/process/called/', $workstation)->getEntity();
        $workstation->hasMatchingProcessScope($cluster);


        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        if ($excludedIds) {
            $exclude = explode(',', $excludedIds);
        }
        $exclude[] = $process->id;

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/called.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'menuActive' => 'workstation',
                'exclude' => join(',', $exclude)
            )
        );
    }
}
