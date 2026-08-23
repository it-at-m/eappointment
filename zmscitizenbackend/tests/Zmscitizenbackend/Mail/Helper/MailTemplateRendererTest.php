<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Mail\Helper;

use BO\Zmscitizenbackend\Mail\Helper\ConfigPreferences;
use BO\Zmscitizenbackend\Mail\Helper\MailTemplateRenderer;
use BO\Zmscitizenbackend\Mail\Helper\ProcessPlainText;
use BO\Zmscitizenbackend\Office\Model\ThinnedContact;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Office\Model\ThinnedProvider;
use BO\Zmscitizenbackend\Office\Model\ThinnedScope;
use PHPUnit\Framework\TestCase;

class MailTemplateRendererTest extends TestCase
{
    public function testRenderIcsUsesCitizenAppointmentNotZmsentitiesProcess(): void
    {
        $renderer = new MailTemplateRenderer(
            [
                'mail_confirmation.twig' => 'Hello {{ client.familyName }}',
                'icsappointment.twig' => "BEGIN:VCALENDAR\nUID:{{ process.id }}\nSUMMARY:{{ process.scope.provider.displayName }}\nEND:VCALENDAR\n",
            ],
            ['appointments' => ['urlAppointments' => 'https://example.test']]
        );

        $ics = $renderer->renderIcs($this->appointment());

        $this->assertNotNull($ics);
        $this->assertStringContainsString('UID:101002', $ics);
        $this->assertStringContainsString('SUMMARY:Bürgerbüro', $ics);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
    }

    public function testRenderMailBuildsSubjectHtmlAndPlainParts(): void
    {
        $renderer = new MailTemplateRenderer(
            [
                'subjects.twig' => 'Terminbestätigung',
                'mail_confirmation.twig' => 'Hello {{ client.familyName }} at {{ process.scope.provider.displayName }}',
                'icsappointment.twig' => "BEGIN:VCALENDAR\nUID:{{ process.id }}\nEND:VCALENDAR\n",
            ],
            ['appointments' => ['urlAppointments' => 'https://example.test']]
        );

        $mail = $renderer->renderMail($this->appointment(), 'appointment');

        $this->assertSame('Terminbestätigung', $mail['subject']);
        $this->assertCount(3, $mail['parts']);
        $this->assertSame('text/html', $mail['parts'][0]['mime']);
        $this->assertStringContainsString('Hello Doe', $mail['parts'][0]['content']);
        $this->assertSame('text/plain', $mail['parts'][1]['mime']);
        $this->assertSame('text/calendar', $mail['parts'][2]['mime']);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $mail['parts'][2]['content']);
    }

    public function testConfigPreferencesNestsDoubleUnderscoreKeys(): void
    {
        $nested = ConfigPreferences::nest([
            'appointments__urlAppointments' => 'https://citizen.example/termin',
        ]);

        $this->assertSame('https://citizen.example/termin', $nested['appointments']['urlAppointments']);
    }

    public function testProcessPlainTextStripsTags(): void
    {
        $this->assertSame("Hello\nWorld", ProcessPlainText::normalize('<b>Hello</b><br>World'));
    }

    private function appointment(): ThinnedProcess
    {
        return new ThinnedProcess(
            processId: 101002,
            timestamp: (string) strtotime('2024-08-29 07:00:00'),
            authKey: 'fb43',
            familyName: 'Doe',
            email: 'johndoe@example.com',
            officeName: 'Bürgerbüro',
            officeId: 102522,
            scope: new ThinnedScope(
                id: 64,
                provider: new ThinnedProvider(
                    id: 102522,
                    name: 'Bürgerbüro Orleansplatz',
                    displayName: 'Bürgerbüro',
                    source: 'dldb',
                    contact: new ThinnedContact(street: 'Orleansstraße', streetNumber: '50')
                ),
                emailFrom: 'no-reply@muenchen.de',
                emailRequired: true
            ),
            serviceId: 1063424,
            serviceName: 'Gewerbe anmelden',
            serviceCount: 1,
            status: 'confirmed'
        );
    }
}
