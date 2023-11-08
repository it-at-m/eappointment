<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of Strings
  * This validation is opinionated: It sanitizes the output from special chars for HTML
  */
class ValidPath extends ValidString
{
    /**
     * Check input, which is intended to be used in a file path
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isPath($message = 'no valid path')
    {
        if ($this->value) {
            $this->isSmallerThan(65536, $message);
            if ($this->value != escapeshellcmd($this->value)) {
                $this->setFailure($message);
            }
            $this->isFreeOf('#\.\.\/#', $message);
        }
        return $this->validate($message, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
