<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Init Controller to display next Button Template only
  *
  */
class WorkstationProcessCancel extends BaseController
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
        $noRedirect = $validator->getParameter('noredirect')->isNumber()->getValue();

        if ($workstation->process['id']) {
            $this->writeCallCount($workstation->process);
            \App::$http->readDeleteResult('/workstation/process/')->getEntity();
        }
        if (1 == $noRedirect) {
            return $response;
        }
        return \BO\Slim\Render::redirect(
            'workstationProcessCallButton',
            array(),
            array()
        );
    }

    protected function writeCallCount($process)
    {
        if (0 < $process->queue['callCount']) {
            $process->queue['callCount']--;
        } else {
            $process->queue['callCount']++;
        }
        \App::$http->readPostResult('/process/'. $process->id .'/'. $process->authKey .'/', $process);
    }
}
