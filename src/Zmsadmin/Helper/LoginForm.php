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
        if ('cluster' == Validator::param('scope')->isString()->getValue()) {
            $collection['scope'] = Validator::param('scope')
                ->isString('Bitte wählen Sie einen Standort aus');
        } else {
            $collection['scope'] = Validator::param('scope')
                ->isNumber('Bitte wählen Sie einen Standort aus');
        }

        if (! Validator::param('appointmentsOnly')->isDeclared()->hasFailed()) {
            $collection['appointmentsOnly'] = Validator::param('appointmentsOnly')
                ->isNumber();
        }


        // workstation
        $collection['workstation'] = Validator::param('workstation')
             ->isString('Bitte wählen Sie einen Arbeitsplatz oder den Tresen aus')
             ->isSmallerThan(5, "Die Arbeitsplatz-Bezeichnung sollte 5 Zeichen nicht überschreiten");

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

        if (isset($workstation->authkey)) {
            Auth::setKey($workstation->authkey);
            return true;
        }
        return false;
    }

    public static function writeWorkstationUpdate($data, $workstation)
    {
        $formData = $data->getValues();
        if (isset($workstation->useraccount)) {
            $workstation->name = $formData['workstation']->getValue();
            if ('cluster' === $formData['scope']->getValue()) {
                $workstation->queue['clusterEnabled'] = 1;
            } else {
                $workstation->queue['clusterEnabled'] = 0;
                $workstation->scope = new \BO\Zmsentities\Scope([
                    'id' => $formData['scope']->getValue(),
                ]);
            }
            if ($formData['appointmentsOnly']) {
                $workstation->queue['appointmentsOnly'] = $formData['appointmentsOnly']->getValue();
            } else {
                $workstation->queue['appointmentsOnly'] = 0;
            }

            unset($workstation->useraccount['departments']);
            $result = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
            error_log(var_export($result, 1));
        }
        return ($result) ? true : false;
    }

    public static function getRedirect($workstation)
    {
        return (0 == $workstation->name) ? 'counter' : 'workstation';
    }
}
