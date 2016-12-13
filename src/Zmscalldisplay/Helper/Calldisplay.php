<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Calldisplay as Entity;

class Calldisplay
{
    public $entity;

    public function __construct($request)
    {
        $this->entity = static::createInstance($request);
        $this->entity = \App::$http->readPostResult('/calldisplay/', $this->entity)->getEntity();
    }

    public function getEntity()
    {
        return $this->entity;
    }

    protected static function createInstance($request)
    {
        $validator = $request->getAttribute('validator');
        $input = $validator->getParameter('calldisplay')->isArray()->getValue();
        $calldisplay = new Entity();
        if ($calldisplay instanceof \BO\Zmsentities\Schema\Entity) {
            $calldisplay->withResolvedCollections($input);
        }
        return $calldisplay;
    }
}
