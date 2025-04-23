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

    protected $fileName = '';

    protected $acceptableTypes = array(
        'pdf' => 'application/pdf',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpg',
        'gif' => 'image/gif',
        'png' => 'image/png'
    );

    public function isFile($name = 'fileupload', $message = 'No valid file found.')
    {
        $this->fileName = $name;
        $this->validated = true;
        if (empty($_FILES) || '' == $_FILES[$name]['tmp_name']) {
            $this->setFailure($message);
        }
        return $this;
    }

    public function hasType($type = 'jpeg', $message = 'Invalid file type.')
    {
        if (isset($_FILES[$this->fileName])) {
            if (
                (! empty($_FILES[$this->fileName]["type"])) &&
                ($_FILES[$this->fileName]['type'] != $this->acceptableTypes[$type])
            ) {
                $this->setFailure($message);
            }
        }
        return $this;
    }

    public function hasMaxSize($maxSize = 50, $message = 'File size not valid.')
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
