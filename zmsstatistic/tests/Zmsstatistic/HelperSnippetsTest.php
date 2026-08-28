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
}
