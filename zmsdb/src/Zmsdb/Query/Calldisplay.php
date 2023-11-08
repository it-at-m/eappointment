<?php

namespace BO\Zmsdb\Query;

class Calldisplay extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'imagedata';

    public function getQueryImage()
    {
        return 'SELECT imagename as name, imagecontent as data FROM `imagedata`
            WHERE `imagename` LIKE :name LIMIT 1';
    }
}
