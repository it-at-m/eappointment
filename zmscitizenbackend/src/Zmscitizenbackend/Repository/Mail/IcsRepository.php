<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository\Mail;

use BO\Slim\LoggerService;
use BO\Zmscitizenbackend\Helper\MailTemplateRenderer;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\Appointment\AppointmentByIdHydrator;

class IcsRepository
{
    private static ?self $instance = null;

    public static function use(?self $repository): void
    {
        self::$instance = $repository;
    }

    public static function create(): self
    {
        return self::$instance ?? new self();
    }

    public function attachIcs(ThinnedProcess $appointment): void
    {
        $icsContent = $this->readIcsContent($appointment);
        if ($icsContent) {
            $appointment->setIcsContent($icsContent);
        }
    }

    public function readIcsContent(ThinnedProcess $appointment): ?string
    {
        $hydrator = new AppointmentByIdHydrator();
        if (!$hydrator->shouldGenerateIcs($appointment->timestamp, $appointment->status)) {
            return null;
        }
        if (($appointment->authKey ?? '') === '') {
            return null;
        }

        try {
            return MailTemplateRenderer::forAppointment($appointment)->renderIcs($appointment);
        } catch (\Exception $exception) {
            LoggerService::logError($exception, null, null, [
                'processId' => $appointment->processId,
                'context' => 'ICS render',
            ]);
            return null;
        }
    }
}
