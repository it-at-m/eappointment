<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsadmin\Helper;

/**
 * Central scale for the Wartende Ampel under Informationen (ZMS-3152).
 * Thresholds match the QAppoint ranges so they can be changed in one place.
 */
class WaitingClientsTrafficLight
{
    public const GREEN_MAX = 10;
    public const YELLOW_MAX = 20;
    public const ORANGE_MAX = 40;

    public const CLASS_GREEN = 'green';
    public const CLASS_YELLOW = 'yellow';
    public const CLASS_ORANGE = 'orange';
    public const CLASS_RED = 'red';

    /**
     * @return array{greenMax: int, yellowMax: int, orangeMax: int}
     */
    public static function thresholds(): array
    {
        return [
            'greenMax' => self::GREEN_MAX,
            'yellowMax' => self::YELLOW_MAX,
            'orangeMax' => self::ORANGE_MAX,
        ];
    }

    public static function cssClassForCount(int $waitingClients): string
    {
        if ($waitingClients <= self::GREEN_MAX) {
            return self::CLASS_GREEN;
        }
        if ($waitingClients <= self::YELLOW_MAX) {
            return self::CLASS_YELLOW;
        }
        if ($waitingClients <= self::ORANGE_MAX) {
            return self::CLASS_ORANGE;
        }
        return self::CLASS_RED;
    }
}
