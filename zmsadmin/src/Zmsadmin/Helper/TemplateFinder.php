<?php

namespace BO\Zmsadmin\Helper;

class TemplateFinder
{
    /**
     * @todo check against ISO definition
     */
    public static function getTemplatePath()
    {
        return realpath(__DIR__) . '/../../../templates';
    }
}
