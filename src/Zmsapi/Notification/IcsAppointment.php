<?php
namespace BO\Zmsapi\Notification;

/**
 *
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 *            A class with a structure of an ics-appointment
 */
class IcsAppointment
{

    public $content = '';

    /**
     * Constructor: ICSappoinment
     * Initializes the object and returns the ics string.
     *
     * Parameters:
     * $process - current process with appointment data
     */
    public function createIcsString(\BO\Zmsentities\Process $process)
    {
        $appointment = $process->getFirstAppointment();
        $date = $appointment->toDateTime();
        $confirmMessage = $this->createConfirmMessage($process);
        ob_start();
        \BO\Slim\Render::html(
            'notification/icsappointment.twig',
            array(
                'date' => $date,
                'startTime' => $appointment->getStartTime(),
                'endTime' => $appointment->getEndTime(),
                'process' => $process,
                'message' => $confirmMessage
            )
        );
            $this->content = \base64_encode(ob_get_contents());
            ob_end_clean();
            return $this;
    }

    protected function createConfirmMessage($process)
    {
        $appointment = $process->getFirstAppointment();
        $client = $process->getFirstClient();
        ob_start();
        \BO\Slim\Render::html(
            'notification/confirmMessage.twig',
            array(
                'date' => $appointment['date'],
                'client' => $client,
                'process' => $process,
                'config' => \BO\Zmsdb\Config::readEntity()
            )
        );
            $confirmMessage = ob_get_contents();
            ob_end_clean();
            return $confirmMessage;
    }
}
