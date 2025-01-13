<?php

namespace BO\Zmsentities\Helper;

/**
 * Special sort algorithm for DLDB
 */
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
