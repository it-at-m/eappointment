<?php

namespace BO\Zmsadmin\Tests\Helper;

use BO\Zmsadmin\Helper\WaitingClientsTrafficLight;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WaitingClientsTrafficLightTest extends TestCase
{
    public function testThresholdsMatchQAppointScale(): void
    {
        $this->assertSame(
            [
                'greenMax' => 10,
                'yellowMax' => 20,
                'orangeMax' => 40,
            ],
            WaitingClientsTrafficLight::thresholds()
        );
    }

    #[DataProvider('countToCssClassProvider')]
    public function testCssClassForCount(int $count, string $expectedClass): void
    {
        $this->assertSame($expectedClass, WaitingClientsTrafficLight::cssClassForCount($count));
    }

    public static function countToCssClassProvider(): array
    {
        return [
            'empty queue is green' => [0, WaitingClientsTrafficLight::CLASS_GREEN],
            'upper green bound' => [10, WaitingClientsTrafficLight::CLASS_GREEN],
            'lower yellow bound' => [11, WaitingClientsTrafficLight::CLASS_YELLOW],
            'upper yellow bound' => [20, WaitingClientsTrafficLight::CLASS_YELLOW],
            'lower orange bound' => [21, WaitingClientsTrafficLight::CLASS_ORANGE],
            'upper orange bound' => [40, WaitingClientsTrafficLight::CLASS_ORANGE],
            'lower red bound' => [41, WaitingClientsTrafficLight::CLASS_RED],
            'high red' => [99999, WaitingClientsTrafficLight::CLASS_RED],
        ];
    }
}
