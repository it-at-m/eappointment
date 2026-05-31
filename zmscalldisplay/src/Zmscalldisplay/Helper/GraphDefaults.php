<?php

namespace BO\Zmscalldisplay\Helper;

class GraphDefaults
{
    protected static function defaultFormat($string): string|null
    {
        return preg_replace('#\s+#m', ' ', trim($string));
    }
}
