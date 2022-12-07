<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \BO\Zmsentities\Workstation;
use \BO\Mellon\Validator;

class Index extends BaseController
{
    protected $withAccess = false;

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }
        $input = $request->getParsedBody();
        if ($request->isPost()) {
            $loginData = $this->testLogin($input);

            if ($loginData instanceof Workstation && $loginData->offsetExists('authkey')) {
                \BO\Zmsclient\Auth::setKey($loginData->authkey);
                return \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }
            \BO\Slim\Render::withHtml(
                $response,
                'page/index.twig',
                array(
                'title' => 'Anmeldung gescheitert',
                'loginfailed' => true,
                'workstation' => null,
                'exception' => $loginData
                )
            );
        }

        $config = (! $workstation)
            ? \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity()
            : null;
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung',
                'config' => $config,
                'workstation' => $workstation
            )
        );
    }
}
