<?php

namespace BO\Zmsbackend\Calldisplay\Repository;

class Calldisplay extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'imagedata';

    public function getQueryImage(): string
    {
        return 'SELECT imagename as name, imagecontent as data FROM `imagedata`
            WHERE `imagename` LIKE :name LIMIT 1';
    }
}
