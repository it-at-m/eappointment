#!/usr/bin/env php
<?php
$dir = realpath(__DIR__);
include($dir . '/cliEnv.php');
$dates = preg_grep('#^\d\d\d\d-\d\d?-\d\d?#', $argv);
if (preg_grep('#^--commit$#', $argv)) {
    $calculator = new \BO\Zmsdb\Helper\CalculateSlots(preg_grep('#^--?v(erbose)?$#', $argv));
    $calculator->writeCalculations(new \DateTimeImmutable(count($dates) ? array_shift($dates) : null));
} else {
    echo "Usage: {$argv[0]} --commit [--verbose] [2018-04-01]\n"
        . "\tCalculates and updates available appointments/slots for availabilities.\n\n";
}
