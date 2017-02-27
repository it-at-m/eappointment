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
        $collection['password'] = Validator::param('password')->isString()
            ->isBiggerThan(2, "Es muss ein Passwort eingegeben werden")
            ->isSmallerThan(40, "Das Passwort sollte 40 Zeichen nicht überschreiten");

        $collection['password_check'] = Validator::param('password_check')->isString();

        if ($collection['password']->getValue() !== $collection['password_check']->getValue()) {
            $collection['password_check']->setFailure('Die Passwörter müssen identisch sein');
        }

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }
}
