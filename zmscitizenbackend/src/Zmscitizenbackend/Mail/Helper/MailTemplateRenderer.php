<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Mail\Helper;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Mail\Repository\MailTemplatesRepository;
use BO\Zmscitizenbackend\Utils\ClientIpHelper;
use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\ArrayLoader;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class MailTemplateRenderer
{
    private const array MAIL_TEMPLATES = [
        'queued' => 'mail_queued.twig',
        'appointment' => 'mail_confirmation.twig',
        'deleted' => 'mail_delete.twig',
        'blocked' => 'mail_delete.twig',
        'preconfirmed' => 'mail_preconfirmed.twig',
    ];

    private const array ICS_TEMPLATES = [
        'appointment' => 'icsappointment.twig',
        'confirmed' => 'icsappointment.twig',
        'deleted' => 'icsappointment_delete.twig',
    ];

    private const array ICS_STATUSES = ['confirmed', 'appointment'];

    /**
     * @param array<string, string> $templates
     * @param array<string, mixed> $config
     */
    public function __construct(private array $templates, private array $config)
    {
    }

    public static function forAppointment(ThinnedProcess $appointment): self
    {
        $providerId = (int) ($appointment->officeId ?: ($appointment->scope?->provider?->id ?? 0));
        $templates = MailTemplatesRepository::create()->readMergedTemplatesForProvider($providerId);

        return new self(MailTemplateProvider::withDefaults($templates)->getTemplates(), ConfigPreferences::read());
    }

    public function renderIcs(ThinnedProcess $appointment, string $status = 'appointment'): ?string
    {
        $template = self::ICS_TEMPLATES[$status] ?? null;
        if ($template === null) {
            return null;
        }

        $html = $this->renderMailHtml($appointment, $status);
        $parameters = $this->icsParameters($appointment, $status, $this->plainText($html));
        $ics = html_entity_decode($this->twig()->render($template, $parameters));

        return $ics !== '' ? self::foldLines($ics) : null;
    }

    /**
     * @return array{
     *     subject: string,
     *     createIP: string,
     *     parts: list<array{mime: string, content: string, base64: bool}>
     * }
     */
    public function renderMail(ThinnedProcess $appointment, string $status): array
    {
        $html = $this->renderMailHtml($appointment, $status);
        $subject = trim($this->twig()->render('subjects.twig', $this->mailParameters($appointment, $status)));
        $parts = [
            ['mime' => 'text/html', 'content' => $html, 'base64' => false],
            ['mime' => 'text/plain', 'content' => $this->plainText($html, "\n"), 'base64' => false],
        ];

        if ($this->shouldAttachIcs($appointment, $status)) {
            $ics = $this->renderIcs($appointment, $status);
            if ($ics) {
                $parts[] = ['mime' => 'text/calendar', 'content' => $ics, 'base64' => false];
            }
        }

        return [
            'subject' => $subject,
            'createIP' => ClientIpHelper::getClientIp(),
            'parts' => $parts,
        ];
    }

    private function renderMailHtml(ThinnedProcess $appointment, string $status): string
    {
        $template = self::MAIL_TEMPLATES[$status] ?? null;
        if ($template === null) {
            throw new \RuntimeException('Mail template for status ' . $status . ' not found');
        }

        return $this->twig()->render($template, $this->mailParameters($appointment, $status));
    }

    private function shouldAttachIcs(ThinnedProcess $appointment, string $status): bool
    {
        if (!in_array($status, self::ICS_STATUSES, true) || !$this->hasAppointmentTime($appointment)) {
            return false;
        }

        $email = (string) ($appointment->email ?? '');
        $blocked = explode(',', (string) ($this->config['notifications']['noAttachmentDomains'] ?? ''));
        foreach ($blocked as $matching) {
            $matching = trim($matching);
            if ($matching !== '' && str_contains($email, '@' . $matching)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function mailParameters(ThinnedProcess $appointment, string $status): array
    {
        $process = $this->processView($appointment);
        $timestamp = $this->startTimestamp($appointment);

        return [
            'date' => $timestamp,
            'getNow' => time(),
            'client' => $process['clients'][0],
            'process' => $process,
            'requestGroups' => $this->requestGroups($appointment),
            'processList' => [],
            'config' => $this->config,
            'initiator' => null,
            'status' => $status,
            'isQueued' => !$this->hasAppointmentTime($appointment),
            'appointmentLink' => base64_encode(json_encode([
                'id' => $appointment->processId ?? '',
                'authKey' => $appointment->authKey ?? '',
            ])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function icsParameters(ThinnedProcess $appointment, string $status, string $message): array
    {
        $start = $this->startTimestamp($appointment);
        $year = $start > 0 ? (int) date('Y', $start) : (int) date('Y');

        return array_merge($this->mailParameters($appointment, $status), [
            'startTime' => $start,
            'endTime' => $start,
            'startSummerTime' => self::summerTimeStart($year)->format('U'),
            'endSummerTime' => self::summerTimeEnd($year)->format('U'),
            'timestamp' => time(),
            'message' => $message,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function processView(ThinnedProcess $appointment): array
    {
        $scope = $appointment->scope;
        $provider = $scope?->provider;
        $contact = $provider?->contact;
        $requests = $this->requests($appointment);

        return [
            'id' => $appointment->processId,
            'displayNumber' => $appointment->displayNumber ?: $appointment->processId,
            'authKey' => $appointment->authKey,
            'createTimestamp' => time(),
            'clients' => [[
                'familyName' => $appointment->familyName,
                'email' => $appointment->email,
                'telephone' => $appointment->telephone,
                'surveyAccepted' => false,
            ]],
            'requests' => $requests,
            'appointments' => [[
                'date' => $this->startTimestamp($appointment),
                'slotCount' => $appointment->slotCount,
            ]],
            'queue' => [
                'status' => $appointment->status,
                'withAppointment' => $this->hasAppointmentTime($appointment),
                'number' => $appointment->displayNumber ?: $appointment->processId,
            ],
            'scope' => [
                'id' => $scope?->id,
                'hint' => $scope?->hint,
                'shortName' => $scope?->shortName,
                'contact' => [
                    'name' => $appointment->officeName,
                ],
                'preferences' => [
                    'client' => [
                        'emailFrom' => $scope?->emailFrom,
                        'emailRequired' => (bool) $scope?->emailRequired,
                    ],
                ],
                'provider' => [
                    'id' => $provider?->id ?? $appointment->officeId,
                    'name' => $provider?->name,
                    'displayName' => $provider?->displayName ?? $appointment->officeName,
                    'source' => $provider?->source,
                    'link' => '',
                    'contact' => [
                        'name' => $contact?->name,
                        'street' => $contact?->street,
                        'streetNumber' => $contact?->streetNumber,
                        'postalCode' => $contact?->postalCode,
                        'city' => $contact?->city,
                        'lat' => $provider?->lat,
                        'lon' => $provider?->lon,
                    ],
                    'data' => [],
                ],
            ],
        ];
    }

    /**
     * @return list<array{id: mixed, name: mixed, source: mixed}>
     */
    private function requests(ThinnedProcess $appointment): array
    {
        $source = $appointment->scope?->provider?->source ?? 'dldb';
        $requests = [];
        for ($i = 0; $i < ($appointment->serviceCount ?? 0); $i++) {
            $requests[] = [
                'id' => $appointment->serviceId,
                'name' => $appointment->serviceName,
                'source' => $source,
            ];
        }
        foreach ($appointment->subRequestCounts ?? [] as $subRequest) {
            $count = (int) ($subRequest['count'] ?? 0);
            for ($i = 0; $i < $count; $i++) {
                $requests[] = [
                    'id' => $subRequest['id'] ?? null,
                    'name' => $subRequest['name'] ?? null,
                    'source' => $source,
                ];
            }
        }

        return $requests;
    }

    /**
     * @return array<int|string, array{request: array<string, mixed>, count: int}>
     */
    private function requestGroups(ThinnedProcess $appointment): array
    {
        $groups = [];
        foreach ($this->requests($appointment) as $request) {
            $id = $request['id'] ?? '';
            if (!isset($groups[$id])) {
                $groups[$id] = ['request' => $request, 'count' => 0];
            }
            $groups[$id]['count']++;
        }

        return $groups;
    }

    private function hasAppointmentTime(ThinnedProcess $appointment): bool
    {
        $timestamp = $this->startTimestamp($appointment);
        if ($timestamp <= 0) {
            return false;
        }

        return date('H:i', $timestamp) !== '00:00';
    }

    private function startTimestamp(ThinnedProcess $appointment): int
    {
        return (int) ($appointment->timestamp ?? 0);
    }

    private function twig(): Environment
    {
        $twig = new Environment(new ArrayLoader($this->templates));
        $twig->addExtension(new TranslationExtension());
        $twig->addExtension(new IntlExtension());

        return $twig;
    }

    private function plainText(string $content, string $lineBreak = "\n"): string
    {
        $converter = new HtmlConverter();
        $converter->getConfig()->setOption('remove_nodes', 'script');
        $converter->getConfig()->setOption('strip_tags', true);
        $converter->getConfig()->setOption('hard_break', true);
        $converter->getConfig()->setOption('use_autolinks', false);
        $text = $converter->convert($content);
        $text = str_replace([',', ';'], ['\,', '\;'], $text);
        $text = str_replace("\n", $lineBreak, $text);

        return trim($text);
    }

    private static function foldLines(string $content): string
    {
        $newLines = [];
        foreach (explode("\n", $content) as $text) {
            $subline = '';
            while (strlen($text) > 75) {
                $line = mb_substr($text, 0, 72);
                $subline .= $line . chr(13) . chr(10) . chr(32);
                $text = mb_substr($text, mb_strlen($line));
            }
            if ($text !== '' && $subline !== '') {
                $subline .= $text;
            }
            if ($subline !== '') {
                $newLines[] = $subline;
            }
            if ($text !== '' && $subline === '') {
                $newLines[] = $text;
            }
        }

        return implode(chr(13) . chr(10), $newLines);
    }

    private static function summerTimeStart(int $year): \DateTime
    {
        return (new \DateTime($year . '-03-01', new \DateTimeZone('Europe/Berlin')))
            ->modify('Last Sunday of March')
            ->setTime(2, 0);
    }

    private static function summerTimeEnd(int $year): \DateTime
    {
        return (new \DateTime($year . '-10-01', new \DateTimeZone('Europe/Berlin')))
            ->modify('Last Sunday of October')
            ->setTime(3, 0);
    }
}
