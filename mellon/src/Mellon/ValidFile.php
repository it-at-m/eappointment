<?php

/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of uploaded files
  *
  * @SuppressWarnings(Superglobals)
  */
class ValidFile extends Valid
{
    /**
     * Allow only valid file
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */

    protected string $fileName = '';

    /**
     * @var array<string, string>
     */
    protected array $acceptableTypes = array(
        'pdf' => 'application/pdf',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    );

    /** @psalm-api */
    public function isFile(string $name = 'fileupload', string $message = 'No valid file found.'): static
    {
        $this->fileName = $name;
        $this->validated = true;
        if (
            $name === ''
            || empty($_FILES)
            || !isset($_FILES[$name]['tmp_name'])
            || $_FILES[$name]['tmp_name'] === ''
        ) {
            $this->setFailure($message);
        }
        return $this;
    }

    /** @psalm-api */
    public function hasType(string $type = 'jpeg', string $message = 'Invalid file type.'): static
    {
        if (isset($_FILES[$this->fileName])) {
            if (
                isset($_FILES[$this->fileName]['type'])
                && $_FILES[$this->fileName]['type'] !== ''
                && $_FILES[$this->fileName]['type'] != $this->acceptableTypes[$type]
            ) {
                $this->setFailure($message);
            }
        }
        return $this;
    }

    /** @psalm-api */
    public function hasMaxSize(int $maxSize = 50, string $message = 'File size not valid.'): static
    {
        $maxByteSize = $maxSize * 1000 * 1024;
        if (isset($_FILES[$this->fileName])) {
            if (($_FILES[$this->fileName]['size'] >= $maxByteSize) || ($_FILES[$this->fileName]["size"] == 0)) {
                $this->setFailure($message);
            }
        }

        return $this;
    }
}
