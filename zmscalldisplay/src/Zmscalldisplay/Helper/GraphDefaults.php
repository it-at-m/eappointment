<?php

namespace BO\Zmscalldisplay\Helper;

class GraphDefaults
{
    protected static function defaultFormat($string)
    {
        return preg_replace('#\s+#m', ' ', trim($string));
    }
}
