<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsclient\Auth;
use BO\Zmsentities\Workstation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Index extends BaseController
{
    protected $withAccess = false;

    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }
        $input = $request->getParsedBody();
        if ($request->getMethod() === 'POST') {
            $loginData = $this->testLogin($input);

            if ($loginData instanceof Workstation && $loginData->offsetExists('authkey')) {
                Auth::setKey($loginData->authkey, time() + \App::SESSION_DURATION);
                return Render::redirect('workstationSelect', array(), array());
            }
            Render::withHtml(
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
        return Render::withHtml(
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
