<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

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

         // hint
         $collection['hint'] = Validator::param('hint')
              ->isString();

        // return validated collection
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
        $formData = $data->getValues();
        if (isset($workstation->useraccount)) {
            $workstation->name = '';
            if ($formData['workstation']->getValue()) {
                $workstation->name = $formData['workstation']->getValue();
            }
            if ($formData['hint']->getValue()) {
                $workstation->hint = $formData['hint']->getValue();
            }
            if ('cluster' === $formData['scope']->getValue()) {
                $workstation->queue['clusterEnabled'] = 1;
            } else {
                $workstation->queue['clusterEnabled'] = 0;
                $workstation->scope = new \BO\Zmsentities\Scope([
                    'id' => $formData['scope']->getValue(),
                ]);
            }
            if (isset($formData['appointmentsOnly']) && $formData['appointmentsOnly']) {
                $workstation->queue['appointmentsOnly'] = $formData['appointmentsOnly']->getValue();
            } else {
                $workstation->queue['appointmentsOnly'] = 0;
            }

            unset($workstation->useraccount['departments']);
            $result = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
        }
        return ($result) ? true : false;
    }
}
