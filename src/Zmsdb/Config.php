<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Config as Entity;

class Config extends Base
{

    /**
     *
     * @return \BO\Zmsentities\Config
     * TODO: get config from db
     */
    public static function readEntity()
    {
        return new Entity();
    }
}
