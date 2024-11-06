<?php
define('ZMS_API_URL', function() {
    $url = filter_var(getenv('ZMS_API_URL'), FILTER_SANITIZE_URL);
    if ($url === false || empty($url)) {
        return 'https://localhost/terminvereinbarung/api/2';
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new \InvalidArgumentException('Invalid ZMS_API_URL provided');
    }
    return $url;
});

define('MAINTENANCE_MODE_ENABLED', function() {
    $value = filter_var(getenv('MAINTENANCE_MODE_ENABLED'), FILTER_SANITIZE_STRING);
    return in_array(strtolower($value), ["1", "true", "yes"], true);
});

class App extends \BO\Zmscitizenapi\Application
{
    /**
     * HTTP url for api
     */
    const ZMS_API_URL = ZMS_API_URL;

    /**
     * Flag for enabling maintenance mode
     */
    const MAINTENANCE_MODE_ENABLED = MAINTENANCE_MODE_ENABLED;
}