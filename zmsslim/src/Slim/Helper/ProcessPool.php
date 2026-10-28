<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

/**
 * Run a handful of PHP workers and wait until every one has finished.
 */
final class ProcessPool
{
    private const int WORKER_DEADLINE_SECONDS = 600;

    /** @var resource|null */
    private static $discard = null;

    public static function workerCount(): int
    {
        $raw = getenv('ZMS_CACHE_WARMUP_WORKERS');
        if ($raw === false || $raw === '') {
            return 4;
        }

        $count = (int) $raw;

        return $count > 0 ? $count : 1;
    }

    public static function phpBinary(): string
    {
        $binary = PHP_BINARY;
        $base = strtolower(basename($binary));
        if ($binary !== '' && is_executable($binary) && !str_contains($base, 'fpm') && !str_contains($base, 'httpd')) {
            return $binary;
        }

        return 'php';
    }

    /**
     * @param list<list<string>> $commands
     * @return list<int> indexes of commands that did not exit 0
     */
    public static function run(array $commands, string $cwd, int $deadlineSeconds = self::WORKER_DEADLINE_SECONDS): array
    {
        if (!function_exists('proc_open') || $commands === []) {
            return array_keys($commands);
        }

        $running = [];
        $failed = [];
        foreach ($commands as $index => $command) {
            $worker = self::open($command, $cwd);
            if ($worker === null) {
                $failed[] = $index;
                continue;
            }
            $running[$index] = $worker;
        }

        $deadline = time() + max(1, $deadlineSeconds);
        try {
            self::wait($running, $failed, $deadline);
        } catch (\Throwable $exception) {
            foreach ($running as $worker) {
                self::finish($worker, true);
            }
            throw $exception;
        }

        sort($failed);

        return $failed;
    }

    /**
     * @param array<int, array{process: resource, stdout: resource, stderr: resource}> $running
     * @param list<int> $failed
     */
    private static function wait(array &$running, array &$failed, int $deadline): void
    {
        while ($running !== []) {
            if (time() >= $deadline) {
                foreach ($running as $index => $worker) {
                    self::finish($worker, true);
                    $failed[] = $index;
                }
                $running = [];
                break;
            }

            self::select($running);
            foreach ($running as $index => $worker) {
                self::forward($worker['stdout'], 'stdout');
                self::forward($worker['stderr'], 'stderr');
                $status = proc_get_status($worker['process']);
                if ($status['running']) {
                    continue;
                }
                self::forward($worker['stdout'], 'stdout');
                self::forward($worker['stderr'], 'stderr');
                self::finish($worker, false);
                if ($status['exitcode'] !== 0) {
                    $failed[] = $index;
                }
                unset($running[$index]);
            }
        }
    }

    /**
     * @param list<string> $command
     * @return array{process: resource, stdout: resource, stderr: resource}|null
     */
    private static function open(array $command, string $cwd): ?array
    {
        $pipes = [];
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $cwd
        );
        if (!is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        return [
            'process' => $process,
            'stdout' => $pipes[1],
            'stderr' => $pipes[2],
        ];
    }

    /**
     * @param array<int, array{process: resource, stdout: resource, stderr: resource}> $running
     */
    private static function select(array $running): void
    {
        $read = [];
        foreach ($running as $worker) {
            if (is_resource($worker['stdout'])) {
                $read[] = $worker['stdout'];
            }
            if (is_resource($worker['stderr'])) {
                $read[] = $worker['stderr'];
            }
        }
        if ($read === []) {
            return;
        }

        $write = null;
        $except = null;
        @stream_select($read, $write, $except, 1);
    }

    /**
     * @param array{process: resource, stdout: resource, stderr: resource} $worker
     */
    private static function finish(array $worker, bool $terminate): void
    {
        if ($terminate && is_resource($worker['process'])) {
            proc_terminate($worker['process']);
        }
        foreach (['stdout', 'stderr'] as $pipe) {
            if (is_resource($worker[$pipe])) {
                fclose($worker[$pipe]);
            }
        }
        if (is_resource($worker['process'])) {
            proc_close($worker['process']);
        }
    }

    /**
     * STDOUT and STDERR exist only in the CLI SAPI. The citizen warmup calls this from PHP-FPM.
     *
     * @return resource|null
     */
    private static function outputStream(string $which)
    {
        if ($which === 'stdout' && defined('STDOUT')) {
            return STDOUT;
        }
        if ($which === 'stderr' && defined('STDERR')) {
            return STDERR;
        }
        if (!is_resource(self::$discard)) {
            $opened = fopen('php://temp', 'w+');
            self::$discard = $opened === false ? null : $opened;
        }

        return self::$discard;
    }

    /**
     * @param resource $from
     */
    private static function forward($from, string $which): void
    {
        if (!is_resource($from)) {
            return;
        }
        $chunk = stream_get_contents($from);
        $to = self::outputStream($which);
        if (!is_string($chunk) || $chunk === '' || !is_resource($to)) {
            return;
        }
        fwrite($to, $chunk);
    }
}
