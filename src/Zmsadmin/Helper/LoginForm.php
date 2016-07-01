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
    public static function fromParameters()
    {
        $collection = array();

        // loginName
        $collection['loginName'] = Validator::param('loginName')->isString()
            ->isBiggerThan(2, "Es muss ein aussagekräftiger Name eingegeben werden")
            ->isSmallerThan(50, "Der Name sollte 50 Zeichen nicht überschreiten");

        // password
        $collection['password'] = Validator::param('password')->isString()
            ->isBiggerThan(2, "Es muss ein aussagekräftiges Passwort eingegeben werden")
            ->isSmallerThan(50, "Das Passwort sollte 50 Zeichen nicht überschreiten");

        // department
        $collection['department'] = Validator::param('department')->isNumber('Bitte wählen Sie eine Behörde aus');

        // scope
        $collection['scope'] = Validator::param('scope')->isNumber('Bitte wählen Sie einen Standort aus');

        // workstation
        if (Validator::param('workstationCounter')->isDeclared()->hasFailed()) {
            $collection['workstation'] = Validator::param('workstation')
                ->isNumber('Bitte wählen Sie eine Platznummer aus oder den Tresen');
        }

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }
}
