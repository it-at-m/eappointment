<?php

namespace BO\Zmsentities\Validator;

use BO\Mellon\Valid;
use BO\Mellon\Unvalidated;
use BO\Mellon\Validator;
use BO\Mellon\Parameter;
use BO\Mellon\Collection;
use BO\Zmsentities\Helper\Delegate;
use BO\Zmsentities\Helper\ProcessPlainText;
use BO\Zmsentities\Process;

/**
 *
 */
class ProcessValidator
{
    protected $process;

    protected $collection = [];

    public function __construct(Process $process)
    {
        $this->process = $process;
        $this->collection = new Collection([]);
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getDelegatedProcess(): Delegate
    {
        $process = $this->getProcess();
        $delegatedProcess = new Delegate($process);
        return $delegatedProcess;
    }

    public function validateId(Unvalidated $unvalid, callable $setter, callable $isRequiredCallback = null): self
    {
        $valid = $unvalid->isNumber(
            "Eine gültige Vorgangsnummer ist in der Regel eine sechsstellige Nummer wie '123456'"
        );
        $length = strlen((string)$valid->getValue());
        if ($length) {
            $valid->isGreaterThan(100000, "Eine Vorgangsnummer besteht aus mindestens 6 Ziffern");
            $valid->isLowerEqualThan(99999999999, "Eine Vorgangsnummer besteht aus maximal 11 Ziffern");
        } elseif (!$length && $isRequiredCallback && $isRequiredCallback()) {
            $valid->isRequired("Eine Vorgangsnummer wird benötigt.");
        }
        $this->getCollection()->validatedAction($valid, $setter);
        return $this;
    }

    public function validateAuthKey(Unvalidated $unvalid, callable $setter, callable $isRequiredCallback = null): self
    {
        $valid = $unvalid->isString();
        $length = strlen((string)$valid->getValue());
        if ($length || ($isRequiredCallback && $isRequiredCallback())) {
            $valid
                ->isBiggerThan(4, "Es müssen mindestens 4 Zeichen eingegeben werden.")
                ;
        }
        $this->getCollection()->validatedAction($valid, $setter);
        return $this;
    }

    public function validateMail(Unvalidated $unvalid, callable $setter, callable $isRequiredCallback = null): self
    {
        $valid = $unvalid->isString();
        $length = strlen((string)$valid->getUnvalidated());
        $process = $this->getProcess();

        /*
        error_log(
            "Mail validate: ".$valid->getUnvalidated()
            ." ($length) with scope mail required="
            . ($process->getCurrentScope()->isEmailRequired() ? 'yes' : 'no')
            ." with appointment="
            . ($process->isWithAppointment() ? 'yes' : 'no')
            ." with callback="
            . ( ($isRequiredCallback && $isRequiredCallback()) ? 'yes' : 'no')
        );
        */
        if (!$length && $process->getCurrentScope()->isEmailRequired() && $process->isWithAppointment()) {
            $valid->isBiggerThan(
                6,
                "Für den Standort muss eine gültige E-Mail Adresse eingetragen werden"
            );
        } elseif (!$length && $isRequiredCallback && $isRequiredCallback()) {
            $valid->isBiggerThan(
                6,
                "Für den Email-Versand muss eine gültige E-Mail Adresse angegeben werden"
            );
        } elseif ($length) {
            $valid = $unvalid
                ->isMail("Die E-Mail Adresse muss im Format max@mustermann.de eingeben werden.")
                ->hasDNS(
                    "Zu der angegebenen E-Mail-Adresse können keine Mails verschickt werden. " .
                    "Der Host zur Domain nach dem '@' ist nicht erreichbar. "
                );
        }
        $this->getCollection()->validatedAction($valid, $setter);
        return $this;
    }

    public function validateName(Unvalidated $unvalid, callable $setter): self
    {
        $valid = $unvalid->isString();
        $length = strlen((string)$valid->getValue());
        if ($length || $this->getProcess()->isWithAppointment()) {
            $valid
                ->isBiggerThan(2, "Es muss ein aussagekräftiger Name eingegeben werden")
                ->isSmallerThan(50, "Der Name sollte 50 Zeichen nicht überschreiten");
        }
        $this->getCollection()->validatedAction($valid, $setter);
        return $this;
    }

    /**
     * Validates a scope custom text field (varchar 255), with optional HTML stripped to plain text.
     */
    public function validateCustomTextfield(Unvalidated $unvalid, callable $setter, bool $required): self
    {
        $valid = $unvalid->isString('Ungültige Zeichenkette', false);
        if ($valid->hasFailed()) {
            $this->getCollection()->validatedAction($valid, $setter);
            return $this;
        }
        $normalized = ProcessPlainText::normalize($valid->getValue());
        if ($required && trim($normalized) === '') {
            $valid->setFailure('Dieses Feld darf nicht leer sein');
        } elseif (mb_strlen($normalized, 'UTF-8') > ProcessPlainText::MAX_CUSTOM_TEXTFIELD_CHARS) {
            $valid->setFailure(
                'Der Eintrag überschreitet die maximal erlaubte Länge von ' .
                ProcessPlainText::MAX_CUSTOM_TEXTFIELD_CHARS .
                ' Zeichen'
            );
        }
        $this->getCollection()->validatedAction($valid, function ($_v) use ($setter, $normalized) {
            $setter($normalized);
        });
        return $this;
    }

    public function validateTelephone(Unvalidated $unvalid, callable $setter): self
    {
        $valid = $unvalid->isString();
        $length = strlen((string)$valid->getValue());

        try {
            $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneNumberUtil->parse($valid->getValue(), 'DE');
            $telephone = '+' . $phoneNumberObject->getCountryCode() . $phoneNumberObject->getNationalNumber();
        } catch (\Exception $exception) {
            $telephone = $valid->getValue();
        }
        $valid = (new \BO\Mellon\Unvalidated($telephone, 'telephone'))->isString();

        if (
            !$length
            && $this->getProcess()->getCurrentScope()->isTelephoneRequired()
            && $this->getProcess()->isWithAppointment()
        ) {
            $valid
                ->isBiggerThan(10, "Für den Standort muss eine gültige Telefonnummer eingetragen werden");
        } elseif ($length) {
            $valid
                ->isSmallerThan(
                    15,
                    "Die Telefonnummer ist zu lang, bitte prüfen Sie Ihre Eingabe"
                )
                ->isBiggerThan(10, "Für den Standort muss eine gültige Telefonnummer eingetragen werden")
                ->isMatchOf("/^\+?[\d\s]*$/", "Die Telefonnummer muss im Format 0170 1234567 eingegeben werden");
        }
        $this->getCollection()->validatedAction($valid, $setter);
        return $this;
    }

    public function validateSurvey(Unvalidated $unvalid, callable $setter): self
    {
        $valid = $unvalid->isNumber("Bitte wählen Sie eine Option");
        $this->getCollection()->validatedAction($valid, $setter);
        return $this;
    }

    public function validateText(Unvalidated $unvalid, callable $setter): self
    {
        $valid = $unvalid->isString('Ungültige Zeichenkette', false);
        if ($valid->hasFailed()) {
            $this->getCollection()->validatedAction($valid, $setter);
            return $this;
        }
        $normalized = ProcessPlainText::normalize($valid->getValue());
        $length = mb_strlen($normalized, 'UTF-8');
        if ($length === 0) {
            $this->getCollection()->addValid($valid);
            return $this;
        }
        if ($length > ProcessPlainText::MAX_AMENDMENT_CHARS) {
            $valid->setFailure('Die Anmerkung sollte 500 Zeichen nicht überschreiten');
        }
        $this->getCollection()->validatedAction($valid, function ($_v) use ($setter, $normalized) {
            $setter($normalized);
        });
        return $this;
    }

    public function validateReminderTimestamp(Unvalidated $unvalid, callable $setter, callable $conditionCallback): self
    {
        $valid = $unvalid->isNumber();
        if ($conditionCallback && $conditionCallback()) {
            $this->getCollection()->validatedAction($valid, $setter);
        } else {
            $this->getCollection()->addValid($valid);
        }
        return $this;
    }

    public function validateRequests(Unvalidated $unvalid, callable $setter): self
    {
        if ($this->getProcess()->isWithAppointment()) {
             $valid = $unvalid->isArray("Es muss mindestens eine Dienstleistung ausgewählt werden!");
             $this->getCollection()->validatedAction($valid, $setter);
        }
        return $this;
    }
}
