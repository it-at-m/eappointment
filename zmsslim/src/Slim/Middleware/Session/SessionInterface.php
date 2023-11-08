<?php

namespace BO\Slim\Middleware\Session;

interface SessionInterface extends \JsonSerializable
{
    public function setGroup(array $group, $clear);

    public function set($key, $value, $groupIndex);

    public function get($key, $groupIndex, $default);

    public function getEntity();

    public function remove($key, $groupIndex);

    public function clear();

    public function clearGroup();

    public function has($key, $groupIndex);

    public function isEmpty();
}
