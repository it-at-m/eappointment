<?php

namespace BO\Slim\Helper;

use BO\Slim\Request;

class TemplateUrls
{
    public static function resolveIncludeUrl(?Request $request = null): string
    {
        return self::resolve($request, true);
    }

    public static function resolveBaseUrl(?Request $request = null): string
    {
        return self::resolve($request, false);
    }

    private static function resolve(?Request $request, bool $withUri): string
    {
        if (null !== \App::$includeUrl) {
            return \App::$includeUrl;
        }

        if (null === $request) {
            return '/';
        }

        $uri = (string) $request->getBasePath();
        if ($withUri) {
            $uri = $request->getBaseUrl();
            $uri = preg_replace('#^https?://[^/]+#', '', $uri);
        }

        return \BO\Slim\Helper::proxySanitizeUri($uri);
    }
}
