<?php
require(__DIR__ . '/../config.php');

\BO\Zmsdb\Helper\DldbData::$dataPath = __DIR__ . '/Zmsdb/fixtures';
\BO\Zmsdb\Helper\DldbData::getDataRepository('de');
