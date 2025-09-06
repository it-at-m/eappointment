<?php

namespace BO\Zmsdb\Query;

class Calldisplay extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'image_data';

    public function getQueryImage()
    {
        return 'SELECT imagename as name, imagecontent as data FROM `image_data`
            WHERE `imagename` LIKE :name LIMIT 1';
    }
}
