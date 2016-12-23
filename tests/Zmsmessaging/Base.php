<?php

namespace BO\Zmsmessaging\Tests;

abstract class Base extends \PHPUnit_Framework_TestCase
{
    protected function writeTestLogin()
    {
        $userAccount = new \BO\Zmsentities\UserAccount(array(
            'id' => 'exampleusername',
            'password' => 'examplepassword'
        ));
        $workstation = \App::$http->readPostResult('/workstation/'. $userAccount->id .'/', $userAccount)->getEntity();
        if (isset($workstation->authKey)) {
            \BO\Zmsclient\Auth::setKey($workstation->authKey);
        }
        return $workstation;
    }

    protected function writeTestLogout()
    {
        \App::$http->readDeleteResult('/workstation/berlinonline/')->getEntity();
    }
}
