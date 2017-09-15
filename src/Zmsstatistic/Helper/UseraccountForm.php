<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Mellon\Validator;

class UseraccountForm
{

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromAddParameters()
    {
        $collection = array();

        // assigend departments
        $collection['departments'] = Validator::param('departments')
            ->isArray('Es muss mindestens eine Behörde oder systemübergreifend ausgewählt werden');
        if (! Validator::param('departments')->isDeclared()->hasFailed() &&
            0 == $collection['departments']->getValue()[0]['id']
        ) {
            $userRights = Validator::param('rights')->isArray()->getValue();
            if (! isset($userRights['superuser']) || ! $userRights['superuser']) {
                $collection['departments']
                    ->setFailure('Für "systemübergreifend" muss die Superuser-Berechtigung ausgewählt werden');
            }
        }

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }
}
