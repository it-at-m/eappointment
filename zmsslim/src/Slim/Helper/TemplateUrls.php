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

        /** @psalm-suppress DeprecatedMethod Template URL helpers still wrap Request base-path helpers. */
        $uri = $request->getBasePath();
        if ($withUri) {
            /** @psalm-suppress DeprecatedMethod Template URL helpers still wrap Request base-url helpers. */
            $uri = $request->getBaseUrl();
            $uri = preg_replace('#^https?://[^/]+#', '', $uri) ?? $uri;
        }

        $resolved = \BO\Slim\Helper::proxySanitizeUri($uri);
        return is_array($resolved) ? implode('', $resolved) : $resolved;
    }
}
