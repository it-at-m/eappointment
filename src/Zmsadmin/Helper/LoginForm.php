<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsclient\Auth;

class LoginForm
{

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromLoginParameters()
    {
        $collection = array();

        // loginName
        $collection['loginName'] = Validator::param('loginName')->isString()
            ->isBiggerThan(2, "Es muss ein aussagekräftiger Name eingegeben werden")
            ->isSmallerThan(250, "Der Name sollte 250 Zeichen nicht überschreiten");

        // password
        $collection['password'] = Validator::param('password')->isString()
            ->isBiggerThan(2, "Es muss ein Passwort eingegeben werden")
            ->isSmallerThan(250, "Das Passwort sollte 250 Zeichen nicht überschreiten");

        // department
        $collection['department'] = Validator::param('department')
            ->isNumber('Bitte wählen Sie eine Behörde aus');

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromAdditionalParameters()
    {
        $collection = array();

        // scope
        $collection['scope'] = Validator::param('scope')
            ->isNumber('Bitte wählen Sie einen Standort aus');

        // workstation
        if (Validator::param('workstationCounter')->isDeclared()->hasFailed()) {
            $collection['workstation'] = Validator::param('workstation')
                ->isString('Bitte wählen Sie einen Arbeitsplatz oder den Tresen aus');
        } else {
            $collection['workstation'] = Validator::param('workstationCounter')
                ->isNumber('Bitte wählen Sie einen Arbeitsplatz oder den Tresen aus');
        }

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }

    public static function setLoginAuthKey($data)
    {
        $loginData = $data->getValues();
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => $loginData['loginName']->getValue(),
            'password' => $loginData['password']->getValue()
        ));
        $workstation = \App::$http->readPostResult(
            '/workstation/'. $userAccount->id .'/',
            $userAccount
        )->getEntity();

        if (isset($workstation->authKey)) {
            Auth::setKey($workstation->authKey);
            return true;
        }
        return false;
    }

    public static function setLoginRedirect($data, $workstation)
    {
        $formData = $data->getValues();
        if (isset($workstation->useraccount)) {
            $workstation->name = $formData['workstation']->getValue();
            $workstation->scope['id'] = $formData['scope']->getValue();
            $workstation = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
            return (0 == $workstation->name) ? 'counter' : 'workstation';
        }
        return false;
    }
}
