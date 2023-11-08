<?php
/**
 *
 * @package Zmsentities
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsentities\Helper;

use BO\Mellon\Validator;
use \BO\Zmsentities\Helper\Property;

/**
 *
 * @SuppressWarnings(Complexity)
 * @todo Deprecated, only for d115 mandant in use, delete after changed to Validator\ProcessValidator
 *
 */
class ProcessFormValidation
{

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromParameters($scopePrefs = array(), $withAppointment = false)
    {
        $collection = array();
        $collection = self::getPersonalParameters($collection, $scopePrefs, $withAppointment);
        $collection = self::getAdditionalParameters($collection);
        $collection = self::getNotificationParameters($collection);

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromAdminParameters($scopePrefs = array(), $withAppointment = false)
    {
        $collection = array();
        $collection = self::getPersonalParameters($collection, $scopePrefs, $withAppointment);
        $collection = self::getAdditionalAdminParameters($collection, $withAppointment);
        $collection = self::getNotificationParameters($collection);
        
        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }

    public static function fromManageProcess()
    {
        // processId
        $collection['process'] = Validator::param('process')
            ->isNumber("Es muss eine valide Vorgangsnummer eingegeben werden");
        // authKey
        $collection['authKey'] = Validator::param('authKey')
            ->isString()
            ->isBiggerThan(4, "Der Absagecode ist nicht korrekt")
            ->isSmallerThan(50, "Der Absagecode ist nicht korrekt");
        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }

    public static function fromParametersToProcess($process, $withAppointment = true)
    {
        $form = self::fromParameters($process->scope['preferences'], $withAppointment);
        $formData = self::setFormStatus($form);
        if (isset($formData['failed']) && ! $formData['failed']) {
            $process = self::addClient($process, $formData);
            $process = self::addReminderTimestamp($process, $formData);
            $process = self::addAmendment($process, $formData);
        }
        return array(
            'process' => $process,
            'formdata' => $formData
        );
    }

    protected static function setFormStatus($form)
    {
        $validate = Validator::param('form_validate')->isBool()->getValue();
        $formData = ($validate) ? $form->getStatus(null, true) : null;
        if (!$form->hasFailed()) {
            $formData['failed'] = false;
        } elseif (null !== $formData) {
            $formData['failed'] = true;
        }
        return $formData;
    }

    protected static function addClient($process, $formData)
    {
        $client = new \BO\Zmsentities\Client();
        foreach ($formData as $key => $item) {
            if ($client->offsetExists($key) && null !== $item['value']) {
                $client[$key] = $item['value'];
            }
        }
        $process->clients = [$client];

        return $process;
    }

    protected static function addReminderTimestamp($process, $formData)
    {
        $process->reminderTimestamp = 0;
        if (isset($formData['headsUpTime'])) {
            $process->reminderTimestamp = $formData['headsUpTime']['value'];
        }
        return $process;
    }

    protected static function addAmendment($process, $formData)
    {
        $process->amendment = '';
        if (isset($formData['amendment'])) {
            $process->amendment = $formData['amendment']['value'];
        }
        return $process;
    }

    protected static function getPersonalParameters($collection, $scopePrefs, $withAppointment = false)
    {
        $collection = static::testName($collection, $withAppointment);
        $collection = static::testMail($collection, $scopePrefs, $withAppointment);
        $collection = static::testTelephone($collection, $scopePrefs, $withAppointment);
        $collection = static::testSurvey($collection);
        return $collection;
    }

    protected static function getNotificationParameters($collection)
    {
        // confirmation notification
        if (1 == Validator::param('sendConfirmation')->isNumber()->getValue()) {
            $collection['sendConfirmation'] = Validator::param('sendConfirmation')->isNumber();
        }

        // confirmation mail
        if (1 == Validator::param('sendMailConfirmation')->isNumber()->getValue()) {
            $collection['sendMailConfirmation'] = Validator::param('sendMailConfirmation')->isNumber();
        }

        // reminder notification
        if (1 == Validator::param('sendReminder')->isNumber()->getValue()) {
            $collection['sendReminder'] = Validator::param('sendReminder')->isNumber();
            $collection['headsUpTime'] = Validator::param('headsUpTime')->isNumber();
        }
        return $collection;
    }

    protected static function testName($collection, $withAppointment)
    {
        $length = strlen(Validator::param('familyName')->isString()->getValue());
        if ($length || $withAppointment) {
            $collection['familyName'] = Validator::param('familyName')->isString()
                ->isBiggerThan(2, "Es muss ein aussagekräftiger Name eingegeben werden")
                ->isSmallerThan(50, "Der Name sollte 50 Zeichen nicht überschreiten");
        }
        return $collection;
    }

    protected static function testMail($collection, $scopePrefs, $withAppointment)
    {
        $length = strlen(Validator::param('email')->isString()->getValue());
        if (self::isMailRequired($scopePrefs) && $withAppointment) {
            $collection['email'] = Validator::param('email')
                ->isMail("Die E-Mail Adresse muss im Format max@mustermann.de eingeben werden.")
                ->isBiggerThan(6, "Für den Standort muss eine gültige E-Mail Adresse eingetragen werden");
        }
        if (self::hasCheckedMail() && !$length && $withAppointment) {
            $collection['email'] = Validator::param('email')
                ->isString()
                ->isBiggerThan(6, "Für den Email-Versand muss eine gültige E-Mail Adresse angegeben werden");
        }

        if ($length) {
            $collection['email'] = Validator::param('email')
                ->isMail("Die E-Mail Adresse muss im Format max@mustermann.de eingeben werden.")
                ->hasDNS(
                    "Zu der angegebenen E-Mail-Adresse können keine Mails verschickt werden. ".
                    "Der Host zur Domain nach dem '@' ist nicht erreichbar. ".
                    ""
                );
        }
        return $collection;
    }

    protected static function testSurvey($collection)
    {
        $length = strlen(Validator::param('email')->isString()->getValue());
        if (self::hasCheckedSurvey() && !$length) {
            $collection['surveyAccepted'] = Validator::param('surveyAccepted')->isNumber();
            $collection['email'] = Validator::param('email')->isString()->isBiggerThan(
                6,
                "Für die Teilnahme an der Umfrage muss eine gültige E-Mail Adresse angegeben werden"
            );
        }
        return $collection;
    }

    protected static function testTelephone($collection, $scopePrefs, $withAppointment)
    {
        $inputNumber = Validator::param('telephone')->isString()->getValue();
        if (! $inputNumber) {
            return $collection;
        }
        $length = strlen($inputNumber);
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($inputNumber, 'DE');
        $phoneNumber = '+'.$phoneNumberObject->getCountryCode() . $phoneNumberObject->getNationalNumber();

        if (self::isPhoneRequired($scopePrefs) && $withAppointment) {
            $collection['telephone'] = Validator::value($phoneNumber, 'telephone')
                ->isString()
                ->isBiggerThan(6, "Zu kurz: für den Standort muss eine gültige Telefonnummer eingetragen werden")
                ->isSmallerThan(15, "Zu lang: für den Standort muss eine gültige Telefonnummer eingetragen werden");
        }

        if (self::hasCheckedSms() && !$length) {
            $collection['telephone'] = Validator::value($phoneNumber, 'telephone')
                ->isString()
                ->isBiggerThan(10, "Zu kurz: für den SMS-Versand muss eine gültige Mobilfunknummer angegeben werden")
                ->isSmallerThan(
                    15,
                    "Zu lang: für den SMS-Versand muss eine gültige Mobilfunknummer angegeben werden"
                );
        }

        if ($length) {
            $collection['telephone'] = Validator::value($phoneNumber, 'telephone')
                ->isString()
                ->isBiggerThan(6, "Zu kurz: für den Standort muss eine gültige Telefonnummer eingetragen werden")
                ->isSmallerThan(15, "Zu lang: für den Standort muss eine gültige Telefonnummer eingetragen werden")
                ->isMatchOf("/^\+?[\d\s]*$/", "Die Telefonnummer muss im Format 01701234567 eingegeben werden");
        }
        return $collection;
    }

    protected static function getAdditionalParameters($collection)
    {
        // amendment
        if (!Validator::param('amendment')->isDeclared()->hasFailed()) {
            $collection['amendment'] = Validator::param('amendment')->isString()
                ->isSmallerThan(300, "Die Anmerkung sollte 300 Zeichen nicht überschreiten");
        }

        // agb gelesen
        $collection['agbgelesen'] = Validator::param('agbgelesen')
            ->isDeclared("Bitte akzeptieren Sie die Nutzungsbedingungen um einen Termin zu vereinbaren!");
        if (!Validator::param('agbgelesen')->isDeclared()->hasFailed()) {
            $collection['agbgelesen'] = Validator::param('agbgelesen')->isNumber();
        }
        return $collection;
    }

    protected static function getAdditionalAdminParameters($collection, $withAppointment = false)
    {
        // amendment
        if (!Validator::param('amendment')->isDeclared()->hasFailed()) {
            $collection['amendment'] = Validator::param('amendment')->isString()
                ->isSmallerThan(300, "Die Anmerkung sollte 300 Zeichen nicht überschreiten");
        }

        // requests
        if ($withAppointment) {
            $collection['requests'] = Validator::param('requests')
                ->isArray("Es muss mindestens eine Dienstleistung ausgewählt werden!");
        }
        return $collection;
    }

    protected static function isMailRequired($scopePrefs)
    {
        return (
            Property::__keyExists('emailRequired', $scopePrefs['client']) &&
            Property::__keyExists('emailFrom', $scopePrefs['client']) &&
            $scopePrefs['client']['emailRequired'] &&
            $scopePrefs['client']['emailFrom']
        );
    }

    protected static function isPhoneRequired($scopePrefs)
    {
        return (
            Property::__keyExists('telephoneRequired', $scopePrefs['client']) &&
            $scopePrefs['client']['telephoneRequired']
        );
    }

    protected static function hasCheckedSms()
    {
        return (
            1 == Validator::param('sendConfirmation')->isNumber()->getValue() ||
            1 == Validator::param('sendReminder')->isNumber()->getValue()
        );
    }

    protected static function hasCheckedMail()
    {
        return (1 == Validator::param('sendMailConfirmation')->isNumber()->getValue());
    }

    protected static function hasCheckedSurvey()
    {
        return (1 == Validator::param('surveyAccepted')->isNumber()->getValue());
    }
}
