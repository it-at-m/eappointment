<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

class Useraccount extends BaseController
{

    /**
     *
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $userAccountList = \App::$http->readGetResult('/useraccount/')->getCollection();

        \BO\Slim\Render::withHtml(
            $response,
            'page/useraccount.twig',
            array (
                'title' => 'Nutzer','menuActive' => 'useraccount',
                'workstation' => $workstation,
                'userAccountList' => $userAccountList,
            )
        );
    }
}
