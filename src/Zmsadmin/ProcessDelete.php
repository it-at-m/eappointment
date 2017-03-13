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
 * Delete a process
 */
class ProcessDelete extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $authKey = Validator::value($args['authKey'])->isString()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/'. $authKey . '/')->getEntity();

        $cluster = null;
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        }
        $workstation->hasMatchingProcessScope($cluster, $process);

        $process = \App::$http->readDeleteResult('/process/'. $process->id .'/'. $process->authKey . '/');
        \App::$http->readPostResult('/process/'. $process->id .'/'. $process->authKey .'/delete/mail/', $process);

        return Helper\Render::redirect(
            $workstation->getRedirect(),
            array(),
            array(
                'success' => 'process_deleted'
            )
        );
    }
}
