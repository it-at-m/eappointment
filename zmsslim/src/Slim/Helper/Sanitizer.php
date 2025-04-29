<?php

namespace BO\Slim\Helper;

/**
 * Utility class for sanitizing sensitive information from logs and error messages
 */
class Sanitizer
{
    /**
     * Sanitize sensitive information from stack traces and error messages
     *
     * @param string $trace The text to sanitize
     * @return string The sanitized text
     */
    public static function sanitizeStackTrace($trace)
    {
        // Replace database credentials
        if (defined('\App::DB_PASSWORD')) {
            $password = \App::DB_PASSWORD;
            // Handle encoded/escaped characters
            $encodedPassword = preg_quote($password, '/');
            $trace = preg_replace('/' . $encodedPassword . '/', '***', $trace);
            // Also replace any URL-encoded versions
            $trace = preg_replace('/' . preg_quote(urlencode($password), '/') . '/', '***', $trace);
            // Handle PDO constructor format
            $trace = preg_replace('/\'' . preg_quote($password, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_USER')) {
            $user = \App::DB_USER;
            $encodedUser = preg_quote($user, '/');
            $trace = preg_replace('/' . $encodedUser . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($user), '/') . '/', '***', $trace);
            // Handle PDO constructor format
            $trace = preg_replace('/\'' . preg_quote($user, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_HOST')) {
            $host = \App::DB_HOST;
            $encodedHost = preg_quote($host, '/');
            $trace = preg_replace('/' . $encodedHost . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($host), '/') . '/', '***', $trace);
            // Handle PDO constructor format
            $trace = preg_replace('/\'' . preg_quote($host, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_NAME')) {
            $dbname = \App::DB_NAME;
            $encodedDbname = preg_quote($dbname, '/');
            $trace = preg_replace('/' . $encodedDbname . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($dbname), '/') . '/', '***', $trace);
            // Handle PDO constructor format
            $trace = preg_replace('/\'' . preg_quote($dbname, '/') . '\'/', '\'***\'', $trace);
        }
        if (defined('\App::DB_PORT')) {
            $port = \App::DB_PORT;
            $encodedPort = preg_quote($port, '/');
            // Replace port in various formats
            $trace = preg_replace('/' . $encodedPort . '/', '***', $trace);
            $trace = preg_replace('/' . preg_quote(urlencode($port), '/') . '/', '***', $trace);
            $trace = preg_replace('/\'' . preg_quote($port, '/') . '\'/', '\'***\'', $trace);
            // Handle port in connection strings
            $trace = preg_replace('/port=' . $encodedPort . '/', 'port=***', $trace);
            $trace = preg_replace('/port=' . preg_quote(urlencode($port), '/') . '/', 'port=***', $trace);
        }

        // Replace connection strings with more robust pattern matching
        $trace = preg_replace('/mysql:host=[^;]+;port=\d+;dbname=[^;]+/', 'mysql:host=***;port=***;dbname=***', $trace);
        $trace = preg_replace('/sqlite:[^;]+/', 'sqlite:***', $trace);

        // Replace any remaining credentials in the format username:password@host:port
        $trace = preg_replace('/[^:\s]+:[^@\s]+@[^:\s]+:\d+/', '***:***@***:***', $trace);

        // Handle PDO constructor format with separate parameters
        $trace = preg_replace('/mysql:dbname=[^;\']+.*?Array/', 'mysql:dbname=***\', \'***\', \'***\', Array', $trace);

        // Additional port sanitization for any remaining port numbers
        $trace = preg_replace('/port=\d+/', 'port=***', $trace);
        $trace = preg_replace('/:\d+(\/|$)/', ':***$1', $trace);

        return $trace;
    }
}
