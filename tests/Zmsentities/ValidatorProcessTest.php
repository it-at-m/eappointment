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

    public function testPhoneNumberValid()
    {
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

        $this->assertEquals($process->toProperty()->telephone->get(), $parameters['telephone']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['telephone']['failed']);
    }

    public function testPhoneNumberUnvalidLength()
    {
        $parameters = [
            'telephone' => '0049 0123 456 789 101',
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
            'Die Telefonnummer darf nicht mehr als 20 Zeichen (inklusive Leerzeichen) enthalten', 
            $collectionStatus['telephone']['messages'][0]
        );
        $this->assertTrue($collectionStatus['telephone']['failed']);
    }

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

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
    }

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
