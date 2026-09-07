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
    public static function getTemplatePath(): string
    {
        $templatePath = realpath(__DIR__);
        return ($templatePath !== false ? $templatePath : __DIR__) . '/../../../templates';
    }
}
