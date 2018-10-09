<?php
/**
 *
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Mellon\Validator;

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
        $collection['scope'] = Validator::param('scope')
          ->isNumber('Bitte wählen Sie einen Standort aus');
        $collection = Validator::collection($collection);
        return $collection;
    }

    public static function fromQuickLogin()
    {
        $loginData = static::fromLoginParameters();
        $additionalData = static::fromAdditionalParameters();
        $collection = array_merge($loginData->getValues(), $additionalData->getValues());
        $collection['redirectUrl'] = Validator::param('url')->isString();
        $collection = Validator::collection($collection);
        return $collection;
    }

    public static function writeWorkstationUpdate($data, $workstation)
    {
        if (isset($workstation->useraccount)) {
            $formData = $data->getValues();
            $workstation->setValidatedScope($formData);
            unset($workstation->useraccount['departments']);
            $result = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
        }
        return ($result) ? true : false;
    }
}
