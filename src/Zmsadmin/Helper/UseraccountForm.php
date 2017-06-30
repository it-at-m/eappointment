<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Mellon\Validator;

class UseraccountForm
{

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromAddParameters()
    {
        $collection = array();

        // loginName
        $collection['id'] = Validator::param('id')->isString()
            ->isBiggerThan(2, "Es muss ein aussagekräftiger Nutzername eingegeben werden")
            ->isSmallerThan(40, "Der Nutzername sollte 40 Zeichen nicht überschreiten");

        // password
        $passwords = Validator::param('changePassword')->isArray()->getValue();

        $collection['password'] = Validator::value($passwords[0])->isString()
            ->isBiggerThan(7, "Es muss ein Passwort mit mindestens 8 Zeichen eingegeben werden");
        $collection['password_check'] = Validator::value($passwords[1])->isString()
            ->isBiggerThan(7, "Das Passwort muss wiederholt werden");

        if ($collection['password']->getValue() !== $collection['password_check']->getValue()) {
            $collection['password_check']->setFailure('Die Passwörter müssen identisch sein');
        }

        // assigend departments
        $collection['departments'] = Validator::param('departments')
            ->isArray('Es muss mindestens eine Behörde oder systemübergreifend ausgewählt werden');
        if (0 == $collection['departments']->isArray()->getValue()[0]) {
            if (! Validator::param('rights')->isArray()->getValue()['superuser']) {
                $collection['departments']
                    ->setFailure('Für "systemübergreifend" muss die Superuser-Berechtigung ausgewählt werden');
            }
        }

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }
}
