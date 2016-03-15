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
     * Initializes the object.
     *
     * Parameters:
     * $date - Datum
     * $location - Ort
     * $subject - Zusammenfassung
     * $description - Beschreibung
     */
    public function __construct(\BO\Zmsentities\Process $process)
    {
        $date = $process->getFirstAppointmentDateTime();
        $this->content = $this->createIcsContent($date, $process);
    }

    /**
     * Function: function_getcontent
     * liefert den ics-Eintrag als String
     *
     * Parameters:
     * none
     *
     * Returns:
     * String im ics-Format
     */
    protected function createIcsContent($date, $process)
    {
        ob_start();
        \BO\Slim\Render::html(
            'page/icsappointment.twig',
            array(
                'date' => $date,
                'process' => $process,
            )
        );
        $icsstring = ob_get_contents();
        ob_end_clean();
        return $icsstring;
    }
}
