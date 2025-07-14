<?php

namespace BO\Zmsadmin\Helper;

use BO\Slim\Helper\BaseChangelogHelper;

class ChangelogHelper extends BaseChangelogHelper
{
    protected function getChangelogPath(): string
    {
        return __DIR__ . '/../../../changelog_build.md';
    }
}
