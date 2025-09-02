<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers\Appointment;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use Slim\Psr7\Response;

class AppointmentIcsController extends BaseController
{
    public function __invoke($request, $response, $args): Response
    {
        $params = $request->getQueryParams();
        $processId = isset($params['processId']) ? (int)$params['processId'] : 0;
        $authKey   = $params['authKey'] ?? null;

        if (!$processId || !$authKey) {
            $payload = ['errors' => [['type' => 'badRequest', 'msg' => 'processId and authKey are required']]];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        $process = ZmsApiFacadeService::getProcessById($processId, $authKey);
        if (!$process || empty($process->id)) {
            $payload = ['errors' => [['type' => 'appointmentNotFound', 'msg' => 'Appointment not found']]];
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        // === Zeiten bestimmen (Start/Ende) ===
        $startTs = (int)($process->appointments[0]->date ?? $process->timestamp ?? 0);
        $tz      = new \DateTimeZone('Europe/Berlin');
        $start   = (new \DateTimeImmutable())->setTimestamp($startTs)->setTimezone($tz);

        // Fallback-Dauer 15 Minuten, falls keine Info vorliegt
        $slotMinutes = (int)($process->appointments[0]->slotCount ?? 0);
        if ($slotMinutes <= 0) {
            $slotMinutes = 15;
        }
        $end = $start->modify("+{$slotMinutes} minutes");

        // === Organizer / Location (einfach gehalten, passt zu deiner twig) ===
        $scope     = $process->scope ?? null;
        $provider  = $scope->provider ?? null;
        $contact   = $provider->contact ?? ($scope->contact ?? null);

        $organizerName  = $provider->displayName ?? $provider->name ?? 'Landeshauptstadt München';
        $organizerEmail = $scope->getEmailFrom() ?? 'no-reply@muenchen.de'; // falls getEmailFrom() nicht existiert -> ersetze durch Feld aus deinem Scope

        $street   = $contact->street      ?? '';
        $streetNo = $contact->streetNumber?? '';
        $plz      = $contact->postalCode  ?? '';
        $city     = $contact->city        ?? 'München';

        $locationLine = trim($organizerName . ', ' . trim($street . ' ' . $streetNo) . ', ' . trim($plz . ' ' . $city), ', ');

        $uid = sprintf('%s-%s', $start->format('Ymd'), $process->id);

        // === Twig-Context gemäß deiner icsappointment.twig ===
        $context = [
            'uid'         => $uid,
            'emailFrom'   => $organizerEmail,
            'summary'     => 'München-Termin: ' . $process->id,
            'description' => 'Online gebuchter Termin',
            'dtstart'     => $start,
            'dtend'       => $end,
            'tz'          => $tz,
            'location'    => $locationLine,
            'geo'         => ['lat' => 48.85299, 'lon' => 2.36885], // falls deine twig diese Keys nutzt
            'process'     => $process,   // optional, falls du Felder direkt aus process in twig nutzt
            'provider'    => $provider,  // optional
            'contact'     => $contact,   // optional
        ];

        /** @var \Twig\Environment $twig */
        $twig = \App::$twig; // wie bei euch üblich
        $ics  = $twig->render('messaging/icsappointment.twig', $context);

        $filename = sprintf('appointment-%s.ics', $process->id);
        $response->getBody()->write($ics);

        return $response
            ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->withStatus(200);
    }
}