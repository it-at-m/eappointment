<?php
declare(strict_types=1);

$repoRoot = dirname(__DIR__, 2);
$zb = $repoRoot . '/zmsbackend';
$zapi = $repoRoot . '/zmsapi';
$zdb = $repoRoot . '/zmsdb';
$src = $zb . '/src/Zmsbackend';
$stats = ['api' => 0, 'service' => 0, 'repository' => 0, 'exception' => 0, 'helper' => 0, 'cli' => 0, 'source' => 0, 'tests' => 0];

function ensureDir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function writeFile(string $path, string $content): void
{
    ensureDir(dirname($path));
    file_put_contents($path, $content);
}

function serviceToDomain(string $class): string
{
    static $map = [
        'DayOff' => 'Dayoff',
        'MailTemplates' => 'Mail',
        'ProcessStatusArchived' => 'Process',
        'ProcessStatusFree' => 'Process',
        'ProcessStatusQueued' => 'Process',
        'Apiclient' => 'Apikey',
        'Closure' => 'Availability',
    ];
    if (isset($map[$class])) {
        return $map[$class];
    }
    if (str_starts_with($class, 'Exchange')) {
        return 'Exchange';
    }
    return $class;
}

function queryEntityToDomain(string $entity): string
{
    static $map = [
        'Apiclient' => 'Apikey',
        'Apikey' => 'Apikey',
        'Apiquota' => 'Apikey',
        'Availability' => 'Availability',
        'Closure' => 'Availability',
        'DayOff' => 'Dayoff',
        'MailQueue' => 'Mail',
        'Mailtemplate' => 'Mail',
        'Mimepart' => 'Mail',
        'UserRole' => 'Role',
        'RolePermission' => 'Role',
        'SlotList' => 'Slot',
        'XRequest' => 'Request',
        'OverviewCalendar' => 'Calendar',
    ];
    if (isset($map[$entity])) {
        return $map[$entity];
    }
    if (str_starts_with($entity, 'Exchange')) {
        return 'Exchange';
    }
    if (str_starts_with($entity, 'ProcessStatus')) {
        return 'Process';
    }
    return $entity;
}

function apiControllerToDomain(string $className): ?string
{
    static $prefixes = [
        'MailTemplates' => 'Mail',
        'MailMerged' => 'Mail',
        'MailCustom' => 'Mail',
        'Mail' => 'Mail',
        'Apikey' => 'Apikey',
        'OverallCalendar' => 'Calendar',
        'Calendar' => 'Calendar',
        'Cluster' => 'Cluster',
        'Config' => 'Config',
        'Dayoff' => 'Dayoff',
        'Department' => 'Department',
        'Organisation' => 'Organisation',
        'Owner' => 'Owner',
        'Process' => 'Process',
        'Provider' => 'Provider',
        'Request' => 'Request',
        'Role' => 'Role',
        'Scope' => 'Scope',
        'Session' => 'Session',
        'Source' => 'Source',
        'Ticketprinter' => 'Ticketprinter',
        'Useraccount' => 'Useraccount',
        'Warehouse' => 'Warehouse',
        'Workstation' => 'Workstation',
        'Calldisplay' => 'Calldisplay',
        'Permission' => 'Permission',
        'Conflict' => 'Process',
        'Appointment' => 'Process',
        'UserQueue' => 'Useraccount',
        'CounterGhost' => 'Workstation',
        'Healthcheck' => 'Status',
        'Status' => 'Status',
        'Availability' => 'Availability',
    ];
    foreach ($prefixes as $prefix => $domain) {
        if (str_starts_with($className, $prefix)) {
            return $domain;
        }
    }
    return null;
}

function exceptionFolderToDomain(string $folder): string
{
    return $folder === 'Dayoff' ? 'Dayoff' : $folder;
}

function fixRelativeReferences(string $content): string
{
    $content = preg_replace_callback(
        '/(?<!\\\\)throw new Exception\\\\Pdo\\\\([A-Za-z0-9]+)/',
        static fn ($m) => 'throw new \\BO\\Zmsbackend\\Exception\\Pdo\\' . $m[1],
        $content
    );
    $content = preg_replace_callback(
        '/(?<!\\\\)(?:throw new |\\$exception = new )Exception\\\\([A-Za-z0-9]+)\\\\([A-Za-z0-9]+)/',
        static fn ($m) => $m[0][0] === 't'
            ? 'throw new \\BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\' . $m[2]
            : '$exception = new \\BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\' . $m[2],
        $content
    );
    foreach ([
        'ClusterWithoutScopes' => 'Cluster',
        'CalendarWithoutScopes' => 'Calendar',
        'MailWritePartFailed' => 'Mail',
    ] as $exceptionClass => $domain) {
        $content = str_replace(
            'throw new Exception\\' . $exceptionClass,
            'throw new \\BO\\Zmsbackend\\' . $domain . '\\Exception\\' . $exceptionClass,
            $content
        );
    }
    $content = preg_replace(
        '/(?<!\\\\)new Helper\\\\/',
        'new \\BO\\Zmsbackend\\Helper\\',
        $content
    );
    $content = preg_replace(
        '/(?<!\\\\)(?<![A-Za-z0-9_])Helper\\\\/',
        '\\BO\\Zmsbackend\\Helper\\',
        $content
    );
    $content = preg_replace(
        '/(?<!\\\\)(?<![A-Za-z0-9_])Response\\\\Message/',
        '\\BO\\Zmsbackend\\Api\\Response\\Message',
        $content
    );
    $content = preg_replace(
        '/(?<!\\\\)new Alias\(/',
        'new \\BO\\Zmsbackend\\Query\\Alias(',
        $content
    );
    $content = str_replace(
        '\\BO\\Zmsbackend\\Slot\\Service\\Slot::TIMESLICE',
        'Slot::TIMESLICE',
        $content
    );
    $content = preg_replace_callback(
        '/(?<!\\\\)(?<!Repository\\\\)(?<!Service\\\\)\b(Department|Provider|Organisation|Owner|Process|Request|Useraccount|Workstation|Availability|Cluster|Apiclient|Apikey|Ticketprinter|DayOff|Mailtemplate|MailQueue|Link|Queue)::(TABLE|getTablename)\b/',
        static function ($m) {
            $domain = queryEntityToDomain($m[1]);
            return '\\BO\\Zmsbackend\\' . $domain . '\\Repository\\' . $m[1] . '::' . $m[2];
        },
        $content
    );
    $content = str_replace(
        '\\BO\\Zmsbackend\\Availability\\Service\\Availability::getJoinExpression',
        '\\BO\\Zmsbackend\\Availability\\Repository\\Availability::getJoinExpression',
        $content
    );
    $content = preg_replace(
        '/new \\\\BO\\\\Zmsbackend\\\\([A-Za-z0-9]+)\\\\Service\\\\([A-Za-z0-9]+)\(\$this/',
        'new \\BO\\Zmsbackend\\$1\\Repository\\$2($this',
        $content
    );
    $content = str_replace(
        [
            'new \\BO\\Zmsbackend\\Scope\\Service\\Scope($this',
            'new \\BO\\Zmsbackend\\Scope\\Repository\\Scope($this',
        ],
        'new \\BO\\Zmsbackend\\Query\\Scope($this',
        $content
    );
    $content = preg_replace(
        '/(?<!\\\\)new Provider\(\$this/',
        'new \\BO\\Zmsbackend\\Provider\\Repository\\Provider($this',
        $content
    );
    $content = preg_replace_callback(
        '/\\\\BO\\\\Zmsdb\\\\Exception\\\\([A-Za-z0-9]+)\\\\/',
        static fn ($m) => '\\BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\',
        $content
    );
    $content = preg_replace_callback(
        '/\\\\BO\\\\Zmsapi\\\\Exception\\\\([A-Za-z0-9]+)\\\\/',
        static fn ($m) => '\\BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\',
        $content
    );
    foreach ([
        'SlotDataEmpty' => 'Slot',
        'SlotDataWithoutPreGeneratedSlot' => 'Slot',
        'MailWritePartFailed' => 'Mail',
        'ClusterWithoutScopes' => 'Cluster',
        'CalendarWithoutScopes' => 'Calendar',
    ] as $exceptionClass => $domain) {
        $content = str_replace(
            '\\BO\\Zmsdb\\Exception\\' . $exceptionClass,
            '\\BO\\Zmsbackend\\' . $domain . '\\Exception\\' . $exceptionClass,
            $content
        );
    }
    return $content;
}

function transformPhp(string $content, ?string $contextDomain = null, string $layer = 'generic'): string
{
    $replacements = [
        '\\BO\\Zmsapi\\BaseController' => '\\BO\\Zmsbackend\\Api\\BaseController',
        'BO\\Zmsapi\\BaseController' => 'BO\\Zmsbackend\\Api\\BaseController',
        '\\BO\\Zmsapi\\Response\\Message' => '\\BO\\Zmsbackend\\Api\\Response\\Message',
        'BO\\Zmsapi\\Response\\Message' => 'BO\\Zmsbackend\\Api\\Response\\Message',
        '\\BO\\Zmsdb\\Connection\\' => '\\BO\\Zmsbackend\\Connection\\',
        'BO\\Zmsdb\\Connection\\' => 'BO\\Zmsbackend\\Connection\\',
        '\\BO\\Zmsdb\\Query\\Builder\\' => '\\BO\\Zmsbackend\\Query\\Builder\\',
        'BO\\Zmsdb\\Query\\Builder\\' => 'BO\\Zmsbackend\\Query\\Builder\\',
        '\\BO\\Zmsdb\\Query\\Base' => '\\BO\\Zmsbackend\\Query\\Base',
        'BO\\Zmsdb\\Query\\Base' => 'BO\\Zmsbackend\\Query\\Base',
        '\\BO\\Zmsdb\\Query\\Alias' => '\\BO\\Zmsbackend\\Query\\Alias',
        'BO\\Zmsdb\\Query\\Alias' => 'BO\\Zmsbackend\\Query\\Alias',
        '\\BO\\Zmsdb\\Query\\Scope' => '\\BO\\Zmsbackend\\Query\\Scope',
        'BO\\Zmsdb\\Query\\Scope' => 'BO\\Zmsbackend\\Query\\Scope',
        '\\BO\\Zmsdb\\Query\\MappingInterface' => '\\BO\\Zmsbackend\\Query\\MappingInterface',
        'BO\\Zmsdb\\Query\\MappingInterface' => 'BO\\Zmsbackend\\Query\\MappingInterface',
        '\\BO\\Zmsdb\\Interfaces\\' => '\\BO\\Zmsbackend\\Interfaces\\',
        'BO\\Zmsdb\\Interfaces\\' => 'BO\\Zmsbackend\\Interfaces\\',
        '\\BO\\Zmsdb\\Exception\\Pdo\\' => '\\BO\\Zmsbackend\\Exception\\Pdo\\',
        'BO\\Zmsdb\\Exception\\Pdo\\' => 'BO\\Zmsbackend\\Exception\\Pdo\\',
        '\\BO\\Zmsdb\\Application' => '\\BO\\Zmsbackend\\Application',
        'BO\\Zmsdb\\Application' => 'BO\\Zmsbackend\\Application',
        '\\BO\\Zmsapi\\Application' => '\\BO\\Zmsbackend\\Application',
        'BO\\Zmsapi\\Application' => 'BO\\Zmsbackend\\Application',
        '\\BO\\Zmsdb\\Helper\\' => '\\BO\\Zmsbackend\\Helper\\',
        'BO\\Zmsdb\\Helper\\' => 'BO\\Zmsbackend\\Helper\\',
        '\\BO\\Zmsapi\\Helper\\' => '\\BO\\Zmsbackend\\Helper\\',
        'BO\\Zmsapi\\Helper\\' => 'BO\\Zmsbackend\\Helper\\',
        '\\BO\\Zmsdb\\Cli\\' => '\\BO\\Zmsbackend\\Cli\\',
        'BO\\Zmsdb\\Cli\\' => 'BO\\Zmsbackend\\Cli\\',
        '\\BO\\Zmsapi\\Cli\\' => '\\BO\\Zmsbackend\\Cli\\',
        'BO\\Zmsapi\\Cli\\' => 'BO\\Zmsbackend\\Cli\\',
        '\\BO\\Zmsdb\\Source\\' => '\\BO\\Zmsbackend\\Source\\',
        'BO\\Zmsdb\\Source\\' => 'BO\\Zmsbackend\\Source\\',
    ];
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);

    $content = preg_replace_callback(
        '/\\\\BO\\\\Zmsapi\\\\Exception\\\\([A-Za-z0-9]+)\\\\/',
        static fn ($m) => '\\BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\',
        $content
    );
    $content = preg_replace_callback(
        '/BO\\\\Zmsapi\\\\Exception\\\\([A-Za-z0-9]+)\\\\/',
        static fn ($m) => 'BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\',
        $content
    );
    $content = preg_replace_callback(
        '/\\\\BO\\\\Zmsdb\\\\Exception\\\\([A-Za-z0-9]+)\\\\/',
        static fn ($m) => '\\BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\',
        $content
    );
    $content = preg_replace_callback(
        '/BO\\\\Zmsdb\\\\Exception\\\\([A-Za-z0-9]+)\\\\/',
        static fn ($m) => 'BO\\Zmsbackend\\' . exceptionFolderToDomain($m[1]) . '\\Exception\\',
        $content
    );

    $content = preg_replace_callback(
        '/use BO\\\\Zmsdb\\\\([A-Za-z0-9]+)( as [^;]+)?;/',
        static function ($m) {
            $class = $m[1];
            $alias = $m[2] ?? '';
            $domain = serviceToDomain($class);
            return 'use BO\\Zmsbackend\\' . $domain . '\\Service\\' . $class . $alias . ';';
        },
        $content
    );

    $content = preg_replace_callback(
        '/use BO\\\\Zmsapi\\\\([A-Za-z0-9]+)( as [^;]+)?;/',
        static function ($m) {
            $class = $m[1];
            $alias = $m[2] ?? '';
            $domain = apiControllerToDomain($class);
            if ($domain === null) {
                return $m[0];
            }
            return 'use BO\\Zmsbackend\\' . $domain . '\\Api\\' . $class . $alias . ';';
        },
        $content
    );

    $content = preg_replace_callback(
        '/\\\\BO\\\\Zmsdb\\\\Query\\\\([A-Za-z0-9]+)/',
        static function ($m) {
            $entity = $m[1];
            if (in_array($entity, ['Base', 'Scope', 'Alias', 'MappingInterface'], true)) {
                return '\\BO\\Zmsbackend\\Query\\' . $entity;
            }
            $domain = queryEntityToDomain($entity);
            return '\\BO\\Zmsbackend\\' . $domain . '\\Repository\\' . $entity;
        },
        $content
    );

    $content = preg_replace_callback(
        '/\\\\BO\\\\Zmsdb\\\\([A-Za-z0-9]+)/',
        static function ($m) {
            $class = $m[1];
            if (in_array($class, ['Query', 'Connection', 'Helper', 'Cli', 'Source', 'Exception', 'Interfaces', 'Application', 'Base'], true)) {
                return $m[0];
            }
            $domain = serviceToDomain($class);
            return '\\BO\\Zmsbackend\\' . $domain . '\\Service\\' . $class;
        },
        $content
    );

    $content = preg_replace_callback(
        '/(?<!\\\\)(?<!Zmsbackend\\\\)(?<!Zmsdb\\\\)\bQuery\\\\(?!Builder\\\\)([A-Za-z0-9]+)/',
        static function ($m) {
            $entity = $m[1];
            if (in_array($entity, ['Base', 'Scope', 'Alias', 'MappingInterface'], true)) {
                return '\\BO\\Zmsbackend\\Query\\' . $entity;
            }
            $domain = queryEntityToDomain($entity);
            return '\\BO\\Zmsbackend\\' . $domain . '\\Repository\\' . $entity;
        },
        $content
    );

    if ($layer === 'repository') {
        $content = preg_replace(
            '/extends Base(\s)/',
            'extends \\BO\\Zmsbackend\\Query\\Base$1',
            $content
        );
    } else {
        $content = preg_replace(
            '/extends Base implements/',
            'extends \\BO\\Zmsbackend\\Base implements',
            $content
        );
        $content = preg_replace(
            '/extends Base(\s)/',
            'extends \\BO\\Zmsbackend\\Base$1',
            $content
        );
    }
    $content = str_replace(
        'implements MappingInterface',
        'implements \\BO\\Zmsbackend\\Query\\MappingInterface',
        $content
    );
    $content = str_replace(
        'implements Interfaces\\ResolveReferences',
        'implements \\BO\\Zmsbackend\\Interfaces\\ResolveReferences',
        $content
    );
    $content = preg_replace(
        '/instanceof Helper\\\\/',
        'instanceof \\BO\\Zmsbackend\\Helper\\',
        $content
    );

    $serviceClasses = [];
    foreach (glob($GLOBALS['zdb'] . '/src/Zmsdb/*.php') as $f) {
        $serviceClasses[] = basename($f, '.php');
    }
    foreach (glob($GLOBALS['zdb'] . '/src/Zmsdb/Exchange*.php') as $f) {
        $serviceClasses[] = basename($f, '.php');
    }
    $serviceClasses = array_unique(array_diff($serviceClasses, ['Application', 'Base']));
    usort($serviceClasses, static fn ($a, $b) => strlen($b) <=> strlen($a));
    foreach ($serviceClasses as $sc) {
        $domain = serviceToDomain($sc);
        $fq = '\\BO\\Zmsbackend\\' . $domain . '\\Service\\' . $sc;
        $content = preg_replace(
            '/(?<!\\\\)(?<!Service\\\\)(?<!Repository\\\\)\bnew ' . preg_quote($sc, '/') . '\(/',
            'new ' . $fq . '(',
            $content
        );
        $content = preg_replace(
            '/(?<!\\\\)(?<!Service\\\\)(?<!Repository\\\\)\b' . preg_quote($sc, '/') . '::/',
            $fq . '::',
            $content
        );
    }

    $tableOnQuery = ['Scope'];
    foreach ($tableOnQuery as $entity) {
        $domain = serviceToDomain($entity);
        $content = str_replace(
            '\\BO\\Zmsbackend\\' . $domain . '\\Service\\' . $entity . '::TABLE',
            '\\BO\\Zmsbackend\\Query\\' . $entity . '::TABLE',
            $content
        );
        $content = preg_replace(
            '/(?<!\\\\)(?<!Service\\\\)(?<!Repository\\\\)(?<!Query\\\\)\b' . preg_quote($entity, '/') . '::TABLE/',
            '\\BO\\Zmsbackend\\Query\\' . $entity . '::TABLE',
            $content
        );
    }

    $tableOnRepository = [
        'Department' => 'Department',
        'Owner' => 'Owner',
        'Organisation' => 'Organisation',
        'Useraccount' => 'Useraccount',
        'Availability' => 'Availability',
        'ProcessStatusArchived' => 'Process',
        'Process' => 'Process',
    ];
    foreach ($tableOnRepository as $entity => $domain) {
        $content = str_replace(
            '\\BO\\Zmsbackend\\' . $domain . '\\Service\\' . $entity . '::TABLE',
            '\\BO\\Zmsbackend\\' . $domain . '\\Repository\\' . $entity . '::TABLE',
            $content
        );
        $content = preg_replace(
            '/(?<!\\\\)(?<!Service\\\\)(?<!Repository\\\\)\b' . preg_quote($entity, '/') . '::TABLE/',
            '\\BO\\Zmsbackend\\' . $domain . '\\Repository\\' . $entity . '::TABLE',
            $content
        );
    }

    $content = str_replace('\\BO\\Zmsbackend\\\\BO\\Zmsbackend\\', '\\BO\\Zmsbackend\\', $content);
    $content = str_replace('BO\\Zmsbackend\\\\BO\\Zmsbackend\\', 'BO\\Zmsbackend\\', $content);
    $content = str_replace('BO\\Zmsbackend\\Builder\\Repository\\Builder', 'BO\\Zmsbackend\\Query\\Builder', $content);
    $content = str_replace('\\BO\\Zmsbackend\\BO\\Zmsbackend\\', '\\BO\\Zmsbackend\\', $content);
    $content = str_replace('\\BO\\Zmsdb\\BO\\Zmsbackend\\', '\\BO\\Zmsbackend\\', $content);
    $content = str_replace('"\\BO\\Zmsdb\\BO\\Zmsbackend\\', '"\\BO\\Zmsbackend\\', $content);
    $content = str_replace('extends \\BO\\Zmsdb\\Base', 'extends \\BO\\Zmsbackend\\Base', $content);

    $content = fixRelativeReferences($content);

    return $content;
}

function copyInfrastructurePhp(string $from, string $to): void
{
    $content = file_get_contents($from);
    $content = preg_replace('/namespace BO\\\\Zmsdb;/', 'namespace BO\\Zmsbackend;', $content);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Query;/', 'namespace BO\\Zmsbackend\\Query;', $content);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Query\\\\Builder;/', 'namespace BO\\Zmsbackend\\Query\\Builder;', $content);
    $content = preg_replace(
        '/namespace BO\\\\Zmsdb\\\\Query\\\\Builder\\\\Dialect;/',
        'namespace BO\\Zmsbackend\\Query\\Builder\\Dialect;',
        $content
    );
    $content = str_replace(['\\BO\\Zmsdb\\', 'BO\\Zmsdb\\'], ['\\BO\\Zmsbackend\\', 'BO\\Zmsbackend\\'], $content);
    $content = fixRelativeReferences($content);
    writeFile($to, $content);
}

$querySkip = ['Base.php', 'Alias.php', 'MappingInterface.php', 'Scope.php'];
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== realpath(__FILE__)) {
    return;
}
$force = in_array('--force', $argv ?? [], true);

if ($force) {
    $keepTop = ['Api', 'Connection', 'Helper', 'Query', 'Exception', 'Interfaces', 'Cli', 'Source'];
    foreach (glob($src . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
        if (!in_array(basename($dir), $keepTop, true)) {
            exec('rm -rf ' . escapeshellarg($dir));
        }
    }
    foreach (glob($src . '/Query/*.php') ?: [] as $file) {
        if (!in_array(basename($file), $querySkip, true)) {
            unlink($file);
        }
    }
    exec('rm -rf ' . escapeshellarg($zb . '/tests/Zmsbackend'));
    ensureDir($zb . '/tests/Zmsbackend');
}

copyInfrastructurePhp($zdb . '/src/Zmsdb/Base.php', $src . '/Base.php');
foreach ($querySkip as $base) {
    copyInfrastructurePhp($zdb . '/src/Zmsdb/Query/' . $base, $src . '/Query/' . $base);
}
$builderSrc = $zdb . '/src/Zmsdb/Query/Builder';
$builderIt = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($builderSrc));
foreach ($builderIt as $builderFile) {
    if (!$builderFile->isFile() || $builderFile->getExtension() !== 'php') {
        continue;
    }
    $rel = substr($builderFile->getPathname(), strlen($builderSrc) + 1);
    copyInfrastructurePhp($builderFile->getPathname(), $src . '/Query/Builder/' . $rel);
}

$queryKeep = array_flip($querySkip);
foreach (glob($zdb . '/src/Zmsdb/Query/*.php') as $file) {
    $base = basename($file);
    if (isset($queryKeep[$base])) {
        continue;
    }
    $entity = basename($file, '.php');
    if (!$force && ($entity === 'Availability' || $entity === 'Closure')) {
        continue;
    }
    $domain = queryEntityToDomain($entity);
    $dest = $src . '/' . $domain . '/Repository/' . $entity . '.php';
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Query;/', 'namespace BO\\Zmsbackend\\' . $domain . '\\Repository;', $content);
    $content = transformPhp($content, $domain, 'repository');
    writeFile($dest, $content);
    $stats['repository']++;
}

$serviceSkip = ['Application.php', 'Base.php'];
foreach (glob($zdb . '/src/Zmsdb/*.php') as $file) {
    $base = basename($file);
    if (in_array($base, $serviceSkip, true)) {
        continue;
    }
    $class = basename($file, '.php');
    $domain = serviceToDomain($class);
    $dest = $src . '/' . $domain . '/Service/' . $class . '.php';
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsdb;/', 'namespace BO\\Zmsbackend\\' . $domain . '\\Service;', $content);
    $content = transformPhp($content, $domain, 'service');
    writeFile($dest, $content);
    $stats['service']++;
}

$apiSkip = ['Application.php', 'BaseController.php', 'Index.php'];
foreach (glob($zapi . '/src/Zmsapi/*.php') as $file) {
    $base = basename($file);
    if (in_array($base, $apiSkip, true)) {
        continue;
    }
    $class = basename($file, '.php');
    $domain = apiControllerToDomain($class);
    if ($domain === null) {
        fwrite(STDERR, "WARN: no API domain for $class\n");
        continue;
    }
    if ($domain === 'Availability' && !$force && is_file($src . '/Availability/Api/' . $base)) {
        continue;
    }
    $dest = $src . '/' . $domain . '/Api/' . $base;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsapi;/', 'namespace BO\\Zmsbackend\\' . $domain . '\\Api;', $content);
    $content = str_replace('extends BaseController', 'extends \\BO\\Zmsbackend\\Api\\BaseController', $content);
    $content = transformPhp($content, $domain, 'api');
    writeFile($dest, $content);
    $stats['api']++;
}

foreach (glob($zapi . '/src/Zmsapi/Exception/*', GLOB_ONLYDIR) as $dir) {
    $folder = basename($dir);
    $domain = exceptionFolderToDomain($folder);
    foreach (glob($dir . '/*.php') as $file) {
        if ($domain === 'Availability' && !$force) {
            continue;
        }
        $dest = $src . '/' . $domain . '/Exception/' . basename($file);
        if (is_file($dest) && !$force) {
            continue;
        }
        $content = file_get_contents($file);
        $content = preg_replace(
            '/namespace BO\\\\Zmsapi\\\\Exception\\\\' . preg_quote($folder, '/') . ';/',
            'namespace BO\\Zmsbackend\\' . $domain . '\\Exception;',
            $content
        );
        $content = transformPhp($content, $domain, 'exception');
        writeFile($dest, $content);
        $stats['exception']++;
    }
}

foreach (glob($zdb . '/src/Zmsdb/Exception/*', GLOB_ONLYDIR) as $dir) {
    $folder = basename($dir);
    if ($folder === 'Pdo') {
        continue;
    }
    $domain = exceptionFolderToDomain($folder);
    foreach (glob($dir . '/*.php') as $file) {
        if ($domain === 'Availability' && !$force && is_file($src . '/Availability/Exception/' . basename($file))) {
            continue;
        }
        $dest = $src . '/' . $domain . '/Exception/' . basename($file);
        if (is_file($dest) && !$force) {
            continue;
        }
        $content = file_get_contents($file);
        $content = preg_replace(
            '/namespace BO\\\\Zmsdb\\\\Exception\\\\' . preg_quote($folder, '/') . ';/',
            'namespace BO\\Zmsbackend\\' . $domain . '\\Exception;',
            $content
        );
        $content = transformPhp($content, $domain);
        writeFile($dest, $content);
        $stats['exception']++;
    }
}

$rootExceptionMap = [
    'MailWritePartFailed.php' => 'Mail',
    'SlotDataWithoutPreGeneratedSlot.php' => 'Slot',
    'SlotDataEmpty.php' => 'Slot',
    'ClusterWithoutScopes.php' => 'Cluster',
    'CalendarWithoutScopes.php' => 'Calendar',
];
foreach ($rootExceptionMap as $file => $domain) {
    $from = $zdb . '/src/Zmsdb/Exception/' . $file;
    if (!is_file($from)) {
        continue;
    }
    $dest = $src . '/' . $domain . '/Exception/' . $file;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($from);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Exception;/', 'namespace BO\\Zmsbackend\\' . $domain . '\\Exception;', $content);
    $content = transformPhp($content, $domain);
    writeFile($dest, $content);
    $stats['exception']++;
}

foreach (glob($zdb . '/src/Zmsdb/Helper/*.php') as $file) {
    $base = basename($file);
    $dest = $src . '/Helper/' . $base;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Helper;/', 'namespace BO\\Zmsbackend\\Helper;', $content);
    $content = transformPhp($content);
    writeFile($dest, $content);
    $stats['helper']++;
}

$zapiHelpers = ['Matching.php', 'ExchangePeriod.php', 'ExchangeAccessFilter.php', 'TicketprinterAccess.php'];
foreach ($zapiHelpers as $base) {
    $from = $zapi . '/src/Zmsapi/Helper/' . $base;
    if (!is_file($from)) {
        continue;
    }
    $dest = $src . '/Helper/' . $base;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($from);
    $content = preg_replace('/namespace BO\\\\Zmsapi\\\\Helper;/', 'namespace BO\\Zmsbackend\\Helper;', $content);
    $content = transformPhp($content);
    writeFile($dest, $content);
    $stats['helper']++;
}

foreach (glob($zdb . '/src/Zmsdb/Cli/*.php') as $file) {
    $base = basename($file);
    $dest = $src . '/Cli/' . $base;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Cli;/', 'namespace BO\\Zmsbackend\\Cli;', $content);
    $content = transformPhp($content);
    writeFile($dest, $content);
    $stats['cli']++;
}
foreach (glob($zapi . '/src/Zmsapi/Cli/*.php') as $file) {
    $base = basename($file);
    $dest = $src . '/Cli/' . $base;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsapi\\\\Cli;/', 'namespace BO\\Zmsbackend\\Cli;', $content);
    $content = transformPhp($content);
    writeFile($dest, $content);
    $stats['cli']++;
}

foreach (glob($zdb . '/src/Zmsdb/Source/*.php') as $file) {
    $base = basename($file);
    $dest = $src . '/Source/' . $base;
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Source;/', 'namespace BO\\Zmsbackend\\Source;', $content);
    $content = transformPhp($content);
    writeFile($dest, $content);
    $stats['source']++;
}

$iface = $src . '/Interfaces/ExchangeSubject.php';
if (!is_file($iface) && is_file($zdb . '/src/Zmsdb/Interfaces/ExchangeSubject.php')) {
    $content = file_get_contents($zdb . '/src/Zmsdb/Interfaces/ExchangeSubject.php');
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Interfaces;/', 'namespace BO\\Zmsbackend\\Interfaces;', $content);
    $content = transformPhp($content);
    writeFile($iface, $content);
}

$skipGlobalTransform = [
    '/Base.php',
    '/Connection/',
    '/Query/Base.php',
    '/Query/Alias.php',
    '/Query/MappingInterface.php',
    '/Query/Scope.php',
    '/Query/Builder/',
];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src));
foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }
    $path = $fileInfo->getPathname();
    if (str_contains($path, '/cache/')) {
        continue;
    }
    $skip = false;
    foreach ($skipGlobalTransform as $needle) {
        if (str_contains($path, $needle)) {
            $skip = true;
            break;
        }
    }
    if ($skip) {
        continue;
    }
    $content = file_get_contents($path);
    $original = $content;
    if (preg_match('#/Zmsbackend/([^/]+)/#', $path, $m)) {
        $contextDomain = $m[1];
    } else {
        $contextDomain = null;
    }
    $content = transformPhp($content, $contextDomain);
    if ($content !== $original) {
        file_put_contents($path, $content);
    }
}

$routing = file_get_contents($zapi . '/routing.php');
$routing = str_replace('\BO\Zmsapi\Index', '\BO\Zmsbackend\Api\Index', $routing);
$routing = preg_replace_callback(
    "/'\\\\BO\\\\Zmsapi\\\\([A-Za-z0-9]+)'/",
    static function ($m) {
        $class = $m[1];
        if ($class === 'Index') {
            return "'\\BO\\Zmsbackend\\Api\\Index'";
        }
        $domain = apiControllerToDomain($class);
        if ($domain === null) {
            fwrite(STDERR, "WARN routing: $class\n");
            return $m[0];
        }
        return "'\\BO\\Zmsbackend\\" . $domain . "\\Api\\" . $class . "'";
    },
    $routing
);
writeFile($zb . '/routing.php', $routing);

$apiTestBase = $zb . '/tests/Zmsbackend/Api/Base.php';
$apiBasePhp = <<<'PHP'
<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Tests\Api;

use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

abstract class Base extends \BO\Slim\PhpUnit\Base
{
    protected $namespace = '';

    public function setUp(): void
    {
        $ref = new \ReflectionClass(static::class);
        $ns = $ref->getNamespaceName();
        $this->namespace = str_replace('BO\\Zmsbackend\\Tests\\', 'BO\\Zmsbackend\\', $ns) . '\\';
        \BO\Zmsbackend\Connection\Select::setTransaction();
        \BO\Zmsbackend\Connection\Select::setProfiling();
    }

    public function tearDown(): void
    {
        User::$workstation = null;
        \BO\Zmsbackend\Connection\Select::writeRollback();
        \BO\Zmsbackend\Connection\Select::closeWriteConnection();
        \BO\Zmsbackend\Connection\Select::closeReadConnection();
    }

    public function readFixture($filename)
    {
        $path = dirname(__FILE__) . '/../fixtures/' . $filename;
        if (!is_readable($path) || !is_file($path)) {
            $path = dirname(__FILE__) . '/fixtures/' . $filename;
        }
        if (!is_readable($path) || !is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }

    protected function setWorkstation(
        $workstationId = 137,
        $loginname = "testuser",
        $scopeId = 143,
        $password = "vorschau"
    ) {
        User::$workstation = new Workstation([
            'id' => $workstationId,
            'useraccount' => new Useraccount([
                'id' => $loginname,
                'password' => md5($password)
            ]),
            'scope' => new Scope([
                'id' => $scopeId,
                'preferences' => [
                    'queue' => [
                        'processingTimeAverage' => 10,
                    ]
                ]
            ])
        ]);
        User::$workstationResolved = 2;
        return User::$workstation;
    }

    protected function setDepartment($departmentId)
    {
        $department = new \BO\Zmsentities\Department([
            'id' => $departmentId,
            'name' => "TestDepartment $departmentId",
        ]);
        User::$workstation->getUseraccount()->addDepartment($department);
        return $department;
    }
}
PHP;
writeFile($apiTestBase, $apiBasePhp);

$fixturesFrom = $zapi . '/tests/Zmsapi/fixtures';
$fixturesTo = $zb . '/tests/Zmsbackend/fixtures';
if (is_dir($fixturesFrom)) {
    ensureDir($fixturesTo);
    foreach (glob($fixturesFrom . '/*') as $fixture) {
        copy($fixture, $fixturesTo . '/' . basename($fixture));
    }
}

foreach (glob($zapi . '/tests/Zmsapi/*Test.php') as $file) {
    $class = basename($file, '.php');
    $controller = str_replace('Test', '', $class);
    if (str_starts_with($controller, 'Availability')) {
        continue;
    }
    $domain = apiControllerToDomain($controller);
    if ($domain === null) {
        continue;
    }
    $dest = $zb . '/tests/Zmsbackend/' . $domain . '/Api/' . $class . '.php';
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsapi\\\\Tests;/', 'namespace BO\\Zmsbackend\\Tests\\' . $domain . '\\Api;', $content);
    $content = str_replace('extends Base', 'extends \\BO\\Zmsbackend\\Tests\\Api\\Base', $content);
    $content = transformPhp($content, $domain);
    writeFile($dest, $content);
    $stats['tests']++;
}

$serviceTestBase = $zb . '/tests/Zmsbackend/Service/Base.php';
if (!is_file($serviceTestBase)) {
    $svcBase = file_get_contents($zdb . '/tests/Zmsdb/Base.php');
    $svcBase = preg_replace('/namespace BO\\\\Zmsdb\\\\Tests;/', 'namespace BO\\Zmsbackend\\Tests\\Service;', $svcBase);
    $svcBase = transformPhp($svcBase);
    writeFile($serviceTestBase, $svcBase);
}

foreach (glob($zdb . '/tests/Zmsdb/*Test.php') as $file) {
    $class = basename($file, '.php');
    $service = str_replace('Test', '', $class);
    if (in_array($service, ['Availability', 'Connection'], true)) {
        continue;
    }
    if (str_starts_with($service, 'Exchange')) {
        $domain = 'Exchange';
    } else {
        $domain = serviceToDomain($service);
    }
    $dest = $zb . '/tests/Zmsbackend/' . $domain . '/Service/' . $class . '.php';
    if (is_file($dest) && !$force) {
        continue;
    }
    $content = file_get_contents($file);
    $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Tests;/', 'namespace BO\\Zmsbackend\\Tests\\' . $domain . '\\Service;', $content);
    $content = str_replace('extends Base', 'extends \\BO\\Zmsbackend\\Tests\\Service\\Base', $content);
    $content = transformPhp($content, $domain);
    writeFile($dest, $content);
    $stats['tests']++;
}

foreach (['Connection', 'Query/Builder'] as $testSub) {
    $fromDir = $zdb . '/tests/Zmsdb/' . $testSub;
    if (!is_dir($fromDir)) {
        continue;
    }
    foreach (glob($fromDir . '/*Test.php') as $file) {
        $dest = $zb . '/tests/Zmsbackend/' . $testSub . '/' . basename($file);
        if (is_file($dest) && !$force) {
            continue;
        }
        $content = file_get_contents($file);
        $content = preg_replace('/namespace BO\\\\Zmsdb\\\\Tests\\\\/', 'namespace BO\\Zmsbackend\\Tests\\', $content);
        $content = transformPhp($content);
        writeFile($dest, $content);
        $stats['tests']++;
    }
}

foreach (['Availability.php', 'Closure.php'] as $dup) {
    $p = $src . '/Query/' . $dup;
    if (is_file($p)) {
        unlink($p);
    }
}

$domains = [];
foreach (glob($src . '/*', GLOB_ONLYDIR) as $d) {
    $name = basename($d);
    if (!in_array($name, ['Api', 'Query', 'Connection', 'Exception', 'Helper', 'Interfaces', 'Cli', 'Source', 'cache'], true)) {
        $domains[] = $name;
    }
}
sort($domains);

$phpCount = (int) trim(shell_exec("find " . escapeshellarg($src) . " -name '*.php' | wc -l"));

echo "Migration complete.\n";
echo json_encode($stats, JSON_PRETTY_PRINT) . "\n";
echo "PHP files under src: $phpCount\n";
echo "Domains: " . implode(', ', $domains) . "\n";
