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

foreach ($resultList as $mail) {
    if (isset($mail['errorInfo'])) {
        if (str_contains($mail['errorInfo'], 'No mail entry found in Database')) {
            \App::$log->notice('Mail queue empty');
        } else {
            \App::$log->error('Mail queue transmission error', ['errorInfo' => $mail['errorInfo']]);
        }
        continue;
    }

    if (isset($mail['mailId'])) {
        \App::$log->info('Mail sent from queue', [
            'mailId' => $mail['mailId'],
            'processId' => $mail['processId'] ?? null,
            'createTimestamp' => $mail['createTimestamp'] ?? null,
        ]);
        if ($verbose) {
            \App::$log->debug('Mail send details', [
                'mailId' => $mail['mailId'],
                'recipients' => $mail['recipients'] ?? null,
                'mime' => isset($mail['mime']) ? trim((string) $mail['mime']) : null,
            ]);
        }
    }
}
