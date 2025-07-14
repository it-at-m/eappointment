<?php

namespace BO\Slim\Helper;

use League\CommonMark\CommonMarkConverter;
use HTMLPurifier;
use HTMLPurifier_Config;

abstract class BaseChangelogHelper
{
    abstract protected function getChangelogPath(): string;
    public function getChangelogHtml(): string
    {
        try {
            $markdown = $this->fetchChangelogFromLocal();
            $converter = new CommonMarkConverter();
            $unsafeHtml = $converter->convert($markdown)->getContent();
            // Post-process headers to move dates in parentheses to <small>(date)</small>
            $unsafeHtml = preg_replace_callback(
                '/<(h[1-3])>(.*?) \((\d{2}\.\d{2}\.\d{4})\)<\/h[1-3]>/',
                function ($matches) {
                    return '<' . $matches[1] . '>' . htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8') . ' <small>(' . htmlspecialchars($matches[3], ENT_QUOTES, 'UTF-8') . ')</small></' . $matches[1] . '>';
                },
                $unsafeHtml
            );
            $purifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
            $safeHtml = $purifier->purify($unsafeHtml);
            return $safeHtml;
        } catch (\Exception $e) {
            if (isset(\App::$log)) {
                \App::$log->error('Failed to fetch changelog: ' . $e->getMessage());
            }
            return '';
        }
    }

    private function fetchChangelogFromLocal(): string
    {
        $localFile = $this->getChangelogPath();
        if (!file_exists($localFile)) {
            throw new \Exception('Local changelog file not found: ' . $localFile);
        }
        return file_get_contents($localFile);
    }
}
