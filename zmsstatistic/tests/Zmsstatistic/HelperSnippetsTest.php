<?php

namespace BO\Zmsstatistic\Tests;

use BO\Zmsstatistic\Helper\ReportHelper;
use PHPUnit\Framework\Attributes\DataProvider;

class HelperSnippetsTest extends \PHPUnit\Framework\TestCase
{
    public function testFormatMinutesToTimeRoundsToNearestSecond(): void
    {
        $this->assertSame('1:07', $this->renderFormatMinutesToTime(1.11));
    }

    public function testFormatMinutesToTimeRoundsUnroundedOverallAverageOnce(): void
    {
        // (1:02 + 2:12 + 0:32) / 3 = 75.33s → 1:15, not 1:16 from rounding minutes first
        $this->assertSame('1:15', $this->renderFormatMinutesToTime((62 + 132 + 32) / 3 / 60));
    }

    #[DataProvider('sharedRoundingValues')]
    public function testUiAndXlsxRoundTheSameSecond(float $minutes, string $ui, string $xlsx): void
    {
        $this->assertSame($ui, $this->renderFormatMinutesToTime($minutes));
        $this->assertSame($xlsx, ReportHelper::formatTimeValue($minutes));
    }

    public static function sharedRoundingValues(): array
    {
        return [
            'nearest second' => [1.11, '1:07', '01:07'],
            // 3.425 min = 205.5s; leftover-seconds rounding used to yield 3:25 in XLSX
            'half-second float trap' => [3.425, '3:26', '03:26'],
            'unrounded overall average' => [(62 + 132 + 32) / 3 / 60, '1:15', '01:15'],
            'zero' => [0.0, '0:00', '00:00'],
        ];
    }

    private function renderFormatMinutesToTime(float $minutes): string
    {
        $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__, 2) . '/templates');
        $twig = new \Twig\Environment($loader);
        $twig->addExtension(
            new \Symfony\Bridge\Twig\Extension\TranslationExtension(
                new \Symfony\Component\Translation\Translator('de')
            )
        );
        $template = $twig->createTemplate(
            "{% import 'element/helper/snippets.twig' as timeutils %}"
            . '{{ timeutils.formatMinutesToTime(minutes) }}'
        );

        return trim($template->render(['minutes' => $minutes]));
    }
}
