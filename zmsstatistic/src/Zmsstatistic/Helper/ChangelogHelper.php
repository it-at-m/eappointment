<?php

namespace BO\Zmsstatistic\Helper;

use BO\Slim\Helper\BaseChangelogHelper;

class ChangelogHelper extends BaseChangelogHelper
{
    #[\Override]
    protected function getChangelogPath(): string
    {
        return __DIR__ . '/../../../changelog_build.md';
    }
}
