<?php

declare(strict_types=1);

$root = dirname(__DIR__) . '/src';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

$replacements = [
    // Doubled Service namespace from bad sed pass
    '/\\\\BO\\\\Zmsbackend\\\\([A-Za-z0-9]+)\\\\Service\\\\\\\\BO\\\\Zmsbackend\\\\\1\\\\Service\\\\([A-Za-z0-9]+)::/'
        => '\\BO\\Zmsbackend\\\\$1\\Service\\$2::',

    // Doubled Repository<-Service namespace (constants on repository)
    '/\\\\BO\\\\Zmsbackend\\\\([A-Za-z0-9]+)\\\\Repository\\\\\\\\BO\\\\Zmsbackend\\\\\1\\\\Service\\\\([A-Za-z0-9]+)::/'
        => '\\BO\\Zmsbackend\\\\$1\\Repository\\$2::',

    // TABLE constants live on Query/Repository, not Service
    '/\\\\BO\\\\Zmsbackend\\\\Scope\\\\Service\\\\Scope::TABLE/'
        => '\\BO\\Zmsbackend\\Query\\Scope::TABLE',
    '/\\\\BO\\\\Zmsbackend\\\\Department\\\\Service\\\\Department::TABLE/'
        => '\\BO\\Zmsbackend\\Department\\Repository\\Department::TABLE',
    '/\\\\BO\\\\Zmsbackend\\\\Owner\\\\Service\\\\Owner::TABLE/'
        => '\\BO\\Zmsbackend\\Owner\\Repository\\Owner::TABLE',
    '/\\\\BO\\\\Zmsbackend\\\\Organisation\\\\Service\\\\Organisation::TABLE/'
        => '\\BO\\Zmsbackend\\Organisation\\Repository\\Organisation::TABLE',
    '/\\\\BO\\\\Zmsbackend\\\\Useraccount\\\\Service\\\\Useraccount::TABLE/'
        => '\\BO\\Zmsbackend\\Useraccount\\Repository\\Useraccount::TABLE',
    '/\\\\BO\\\\Zmsbackend\\\\Availability\\\\Service\\\\Availability::TABLE/'
        => '\\BO\\Zmsbackend\\Availability\\Repository\\Availability::TABLE',
    '/\\\\BO\\\\Zmsbackend\\\\Process\\\\Service\\\\ProcessStatusArchived::TABLE/'
        => '\\BO\\Zmsbackend\\Process\\Repository\\ProcessStatusArchived::TABLE',

    // Zmsclient Status helper must stay external
    '/\\\\BO\\\\Zmsclient\\\\\\\\BO\\\\Zmsbackend\\\\Status\\\\Service\\\\\\\\BO\\\\Zmsbackend\\\\Status\\\\Service\\\\Status::/'
        => '\\BO\\Zmsclient\\Status::',
];

$changed = 0;
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;
    foreach ($replacements as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    if ($content !== $original) {
        file_put_contents($path, $content);
        $changed++;
    }
}

echo "Fixed $changed files\n";
