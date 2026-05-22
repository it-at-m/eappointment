<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

$send = preg_grep('#--?s(end)?#', $argv);
$verbose = (preg_grep('#^--?v(erbose)?$#', $argv)) ? true : false;

\App::$messaging = new \BO\Zmsmessaging\Mail($verbose);

$now = new \DateTimeImmutable();
if (class_exists('\App') && isset(\App::$now)) {
    $now = \App::$now;
}
$resultList = \App::$messaging->initQueueTransmission($send);
if (! $send) {
    \App::$log->notice('Use with --send to send emails.');
}

if ($verbose) {
    foreach ($resultList as $mail) {
        if (isset($mail['errorInfo'])) {
            \App::$log->error('Mail queue transmission error', ['errorInfo' => $mail['errorInfo']]);
        } else {
            \App::$log->info('Test mail sent successfully', ['mailId' => $mail['id'] ?? null]);
        }
    }
}
