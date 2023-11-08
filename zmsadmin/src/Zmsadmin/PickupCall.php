<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Mellon\Validator;

/**
  *
  */
class PickupCall extends BaseController
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
        $inputNumber = Validator::value($args['id'])->isNumber()->getValue();
        $process = $this->readSelectedProcess($workstation, $inputNumber);
        if ('pending' != $process->getStatus() && 'pickup' != $process->getStatus()) {
            throw new Exception\Process\ProcessNotPending();
        }
        $workstation->process = \App::$http->readPostResult('/process/status/pickup/', $process)->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/called.twig',
            array(
                'workstation' => $workstation            )
        );
    }

    protected function readSelectedProcess($workstation, $inputNumber)
    {
        try {
            if (4 <= strlen((string)$inputNumber)) {
                $process = \App::$http
                    ->readGetResult('/process/'. $inputNumber .'/')
                    ->getEntity();
                $workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
            } else {
                $process = \App::$http
                    ->readGetResult('/scope/'. $workstation->scope['id'] .'/queue/'. $inputNumber .'/')
                    ->getEntity();
            }
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template == 'BO\Zmsapi\Exception\Process\ProcessByQueueNumberNotFound') {
                $process = new \BO\Zmsentities\Process();
                $process->scope['id'] = $workstation->scope['id'];
                $process->queue['number'] = $inputNumber;
                $process->amendment = 'Über die direkte Nummerneingabe angelegter Abholer.';
                $process = \App::$http->readPostResult('/process/status/pickup/', $process)->getEntity();
            } else {
                throw $exception;
            }
        }
        return $process;
    }
}
