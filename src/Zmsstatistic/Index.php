<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \BO\Zmsstatistic\Helper\LoginForm;
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
            $this->workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\Throwable $workstationexception) {
            $this->workstation = null;
        }

        $form = LoginForm::fromLoginParameters();
        $validate = Validator::param('login_form_validate')->isBool()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;
        if ($loginData && !$form->hasFailed()) {
            return $this->testLogin($loginData, $response);
        }
        $config = (! $this->workstation)
            ? \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity()
            : null;
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung',
                'loginfailed' => Validator::param('login_failed')->isBool()->getValue(),
                'config' => $config,
                'workstation' => $this->workstation,
                'loginData' => $loginData
            )
        );
    }
}
