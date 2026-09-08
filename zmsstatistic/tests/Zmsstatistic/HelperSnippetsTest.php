<?php

namespace BO\Zmsstatistic\Tests;

class HelperSnippetsTest extends \PHPUnit\Framework\TestCase
{
    public function testFormatMinutesToTimeRoundsToNearestSecond(): void
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
            . '{{ timeutils.formatMinutesToTime(1.11) }}'
        );

        $this->assertSame('1:07', trim($template->render()));
    }

    public function testFormatMinutesToTimeRoundsUnroundedOverallAverageOnce(): void
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

        // (1:02 + 2:12 + 0:32) / 3 = 75.33s → 1:15, not 1:16 from rounding minutes first
        $this->assertSame('1:15', trim($template->render(['minutes' => (62 + 132 + 32) / 3 / 60])));
    }
}
