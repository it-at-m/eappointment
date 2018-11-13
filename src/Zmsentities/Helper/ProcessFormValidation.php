<?php
/**
 *
 * @package Zmsentities
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsentities\Helper;

use BO\Mellon\Validator;

class ProcessFormValidation
{

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromParameters($scopePrefs = array())
    {
        $collection = array();
        $collection = self::getPersonalParameters($collection, $scopePrefs);
        $collection = self::getNotificationParameters($collection);
        $collection = self::getAdditionalParameters($collection);

        // return validated collection
        $collection = Validator::collection($collection);
        return $collection;
    }

    /**
     * form data for reuse in multiple controllers
     */
    public static function fromAdminParameters($scopePrefs = array())
    {
        $collection = array();
        $collection = self::getPersonalParameters($collection, $scopePrefs);
        $collection = self::getNotificationParameters($collection);
        $collection = self::getAdditionalAdminParameters($collection);

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

    public static function fromParametersToProcess($process)
    {
        $form = self::fromParameters($process->scope['preferences']);
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
            if (null !== $item['value'] && array_key_exists($key, $client)) {
                $client[$key] = $item['value'];
            }
        }
        $process->clients = array();
        $process->clients[] = $client;
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

    protected static function getPersonalParameters($collection, $scopePrefs)
    {
        // name
        $collection['familyName'] = Validator::param('familyName')->isString()
            ->isBiggerThan(2, "Es muss ein aussagekräftiger Name eingegeben werden")
            ->isSmallerThan(50, "Der Name sollte 50 Zeichen nicht überschreiten");
        // email
        if (!Validator::param('email')->isDeclared()->hasFailed()) {
            $collection['email'] = Validator::param('email')
                ->isMail("Die E-Mail Adresse muss im Format max@mustermann.de eingeben werden.")
                ->hasDNS(
                    "Zu der angegebenen E-Mail-Adresse können keine Mails verschickt werden. ".
                    "Der Host zur Domain nach dem '@' ist nicht erreichbar. ".
                    ""
                );
            if (array_key_exists('emailRequired', $scopePrefs['client']) &&
                $scopePrefs['client']['emailRequired']
            ) {
                $collection['email']
                    ->isBiggerThan(2, "Für den Standort muss eine gültige E-Mail Adresse eingetragen werden");
            }
        }
        // telephone
        if (!Validator::param('telephone')->isDeclared()->hasFailed()) {
            $collection['telephone'] = Validator::param('telephone')
                ->isString()
                ->isMatchOf("/^\+?[\d\s]*$/", "Die Telefonnummer muss im Format 0170 1234567 eingegeben werden");
            if (self::isPhoneRequired($scopePrefs)) {
                $collection['telephone']
                    ->isBiggerThan(2, "Für den Standort muss eine gültige Telefonnummer eingetragen werden");
            }
            if (self::hasCheckedSms()) {
                $collection['telephone']
                    ->isBiggerThan(2, "Für den SMS-Versand muss eine gültige Mobilfunknummer angegeben werden");
            }
        }

        // survey accepted
        if (1 == Validator::param('surveyAccepted')->isNumber()->getValue()) {
            $collection['surveyAccepted'] = Validator::param('surveyAccepted')->isNumber();
        }
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

    protected static function getAdditionalAdminParameters($collection)
    {
        // amendment
        if (!Validator::param('amendment')->isDeclared()->hasFailed()) {
            $collection['amendment'] = Validator::param('amendment')->isString()
                ->isSmallerThan(300, "Die Anmerkung sollte 300 Zeichen nicht überschreiten");
        }

        // requests
        if (!Validator::param('requests')->isDeclared()->hasFailed()) {
            $collection['requests'] = Validator::param('requests')
                ->isArray("Es muss mindestens eine Dienstleistung ausgewählt werden!");
        }
        return $collection;
    }

    protected static function isPhoneRequired($scopePrefs)
    {
        return (
            array_key_exists('telephoneRequired', $scopePrefs['client']) &&
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
}
