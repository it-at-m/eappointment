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
        if (Validator::param('password')->isString()->getValue() ||
            Validator::param('password_check')->isString()->getValue()
        ) {
            $collection['password'] = Validator::param('password')->isString()
                ->isBiggerThan(2, "Es muss ein Passwort eingegeben werden")
                ->isSmallerThan(40, "Das Passwort sollte 40 Zeichen nicht überschreiten");

            $collection['password_check'] = Validator::param('password_check')->isString();

            if ($collection['password']->getValue() !== $collection['password_check']->getValue()) {
                $collection['password_check']->setFailure('Die Passwörter müssen identisch sein');
            }
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
