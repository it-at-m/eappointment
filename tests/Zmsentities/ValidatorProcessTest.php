<?php

namespace BO\Zmsentities\Tests;

use \BO\Zmsentities\Process;
use \BO\Zmsentities\Helper\Delegate;
use \BO\Zmsentities\Validator\ProcessValidator;
use \BO\Mellon\Condition;
use BO\Mellon\Validator;

/**
 *
 */
class ValidatorProcessTest extends Base
{
    public function testInit()
    {
        $process = new Process();
        $processValidator = new ProcessValidator($process);
        $this->assertInstanceof(Process::class, $processValidator->getProcess());
        $this->assertInstanceof(Delegate::class, $processValidator->getDelegatedProcess());
    }

    public function testProcessCredentials()
    {
        $parameters = [
            'id' => '123456',
            'authKey' => '1234'
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateId(
            $validator->getParameter('id'),
            $delegatedProcess->setter('id')
        );

        $processValidator->validateAuthKey(
            $validator->getParameter('authKey'),
            $delegatedProcess->setter('authKey')
        );

        $this->assertEquals($process->getId(), $parameters['id']);
        $this->assertEquals($process->getAuthKey(), $parameters['authKey']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['id']['failed']);
        $this->assertFalse($collectionStatus['authKey']['failed']);
    }

    public function testProcessCredentialsFailed()
    {
        $parameters = [
            'id' => '12345',
            'authKey' => '123'
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateId(
            $validator->getParameter('id'),
            $delegatedProcess->setter('id')
        );

        $processValidator->validateAuthKey(
            $validator->getParameter('authKey'),
            $delegatedProcess->setter('authKey')
        );

        $this->assertNotEquals($process->getId(), $parameters['id']);
        $this->assertNotEquals($process->getAuthKey(), $parameters['authKey']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertTrue($collectionStatus['id']['failed']);
        $this->assertTrue($collectionStatus['authKey']['failed']);
    }

    public function testProcessIdDigitsOnly()
    {
        $parameters = [
            'id' => '1234a'
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateId(
            $validator->getParameter('id'),
            $delegatedProcess->setter('id')
        );

        $this->assertNotEquals($process->getId(), $parameters['id']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertEquals(
            "Eine gültige Vorgangsnummer ist in der Regel eine sechsstellige Nummer wie '123456'",
            $collectionStatus['id']['messages'][0]->message
        );
    }

    public function testProcessIdGreaterThan()
    {
        $parameters = [
            'id' => '99999'
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateId(
            $validator->getParameter('id'),
            $delegatedProcess->setter('id')
        );

        $this->assertNotEquals($process->getId(), $parameters['id']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertEquals(
            "Eine Vorgangsnummer besteht aus mindestens 6 Ziffern",
            $collectionStatus['id']['messages'][0]->message
        );
    }

    public function testProcessIdLowerEqualThan()
    {
        $parameters = [
            'id' => '100000000001'
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateId(
            $validator->getParameter('id'),
            $delegatedProcess->setter('id')
        );

        $this->assertNotEquals($process->getId(), $parameters['id']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertEquals(
            "Eine Vorgangsnummer besteht aus maximal 11 Ziffern",
            $collectionStatus['id']['messages'][0]->message
        );
    }

    public function testProcessIdMissing()
    {
        $parameters = [
            'id' => null
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateId(
            $validator->getParameter('id'),
            $delegatedProcess->setter('id'),
            function () {
                return true;
            }
        );

        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertEquals(
            "Eine Vorgangsnummer wird benötigt.",
            $collectionStatus['id']['messages'][0]->message
        );
    }

    public function testPhoneNumberValid()
    {
        $parsedNumber = '+4912345678910';
        $parameters = [
            'telephone' => '0049 0123 456 789 10',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateTelephone(
            $validator->getParameter('telephone'),
            $delegatedProcess->setter('telephone')
        );

        $this->assertNotEquals($process->toProperty()->telephone->get(), $parameters['telephone']);
        $this->assertEquals($process->toProperty()->telephone->get(), $parsedNumber);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['telephone']['failed']);
    }

    public function testPhoneNumberUnvalidLength()
    {
        $parsedNumber = '+491234567891012';
        $parameters = [
            'telephone' => '0049 0123 456 789 1012',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateTelephone(
            $validator->getParameter('telephone'),
            $delegatedProcess->setter('telephone')
        );

        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertEquals(
            'Die Telefonnummer ist zu lang, bitte prüfen Sie Ihre Eingabe',
            $collectionStatus['telephone']['messages'][0]
        );
        $this->assertTrue($collectionStatus['telephone']['failed']);
    }

    /*
    public function testMail()
    {
        $parameters = [
            'mail' => 'test@berlinonline.de',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail')
        );
        //$this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']); Mail DNS
    }*/

    public function testMailFormat()
    {
        $parameters = [
            'mail' => 'test#berlinonline.de',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail')
        );

        $this->assertNotEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertTrue($collectionStatus['mail']['failed']);
    }

    public function testMailRequiredByParameter()
    {
        $parameters = [
            'sendMailConfirmation' => 1,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $process->getFirstAppointment()->setTime("2016-05-30 11:00:00");
        $process->getCurrentScope()->preferences['client']['emailRequired'] = 0;
        $process->getCurrentScope()->preferences['client']['emailFrom'] = 'test@dummy.test';
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail'),
            new Condition(
                $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1)
            )
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertTrue($collectionStatus['mail']['failed']);
    }

    public function testMailNotRequiredByParameter()
    {
        $parameters = [
            'sendMailConfirmation' => 0,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $process->getFirstAppointment()->setTime("2016-05-30 11:00:00");
        $process->getCurrentScope()->preferences['client']['emailRequired'] = 0;
        $process->getCurrentScope()->preferences['client']['emailFrom'] = 'test@dummy.test';
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail'),
            new Condition(
                $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1)
            )
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['mail']['failed']);
    }


    public function testMailNotRequiredByFrom()
    {
        $parameters = [
            'sendMailConfirmation' => 0,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $process->getFirstAppointment()->setTime("2016-05-30 11:00:00");
        $process->getCurrentScope()->preferences['client']['emailRequired'] = 1;
        $process->getCurrentScope()->preferences['client']['emailFrom'] = '';
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail'),
            new Condition(
                $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1)
            )
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['mail']['failed']);
    }

    public function testMailNotRequiredByMissingTime()
    {
        $parameters = [
            'sendMailConfirmation' => 0,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $process->getCurrentScope()->preferences['client']['emailRequired'] = 1;
        $process->getCurrentScope()->preferences['client']['emailFrom'] = 'test@dummy.test';
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail'),
            new Condition(
                $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1)
            )
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['mail']['failed']);
    }

    public function testMailRequiredByScope()
    {
        $parameters = [
            'sendMailConfirmation' => 0,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $process->getFirstAppointment()->setTime("2016-05-30 11:00:00");
        $process->getCurrentScope()->preferences['client']['emailRequired'] = 1;
        $process->getCurrentScope()->preferences['client']['emailFrom'] = 'test@dummy.test';
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail')
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertTrue($collectionStatus['mail']['failed']);
    }
}
