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
        ob_start();
        \BO\Slim\Render::html(
            'notification/icsappointment.twig',
            array(
                'date' => $appointment->toDateTime(),
                'startTime' => $appointment->getStartTime(),
                'endTime' => $appointment->getEndTime(),
                'process' => $process,
                'message' => $this->createConfirmMessage($process)
            )
        );
            $result = \html_entity_decode(ob_get_contents());
            $this->content = $result;
            ob_end_clean();
            return $this;
    }

    public function createConfirmMessage($process)
    {
        $appointment = $process->getFirstAppointment();
        ob_start();
        \BO\Slim\Render::html(
            'notification/confirmMessage.twig',
            array(
                'date' => $appointment->toDateTime()->format('U'),
                'client' => $process->getFirstClient(),
                'process' => $process,
                'config' => \BO\Zmsdb\Config::readEntity()
            )
        );
            $confirmMessage = ob_get_contents();
            ob_end_clean();
            return $confirmMessage;
    }
}
