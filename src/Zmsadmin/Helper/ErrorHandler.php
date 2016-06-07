<?php

/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 *
 */
namespace BO\Zmsadmin\Helper;

use Psr\Http\Message\RequestInterface;
use BO\Mellon\Validator;

class ErrorHandler extends ErrorChecks
{
    public $error = '';
    public $notice = '';
    public $success = '';
    public $callingClass = '';

    protected $request = null;

    /**
     *
     * @return self
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getErrorResponse()
    {
        $errorMethode = 'handle' . $this->callingClass;
        if (is_callable(array($this, $errorMethode))) {
            return $this->$errorMethode();
        }
    }

    public function hasErrors(array $checkThis, $step = '')
    {
        $error = Validator::param('error')->isString()->getValue();
        if (null !== $error) {
            return $error;
        }
        $errorList = [
            //'is_overaged'       => ['call' => 'isInvalidSession', 'redirect' => 'termin'],
        ];
        $checkList = \array_intersect_key($errorList, array_flip($checkThis));
        return $this->getCheckListRedirect($checkList, $step, 'error');
    }

    public function hasNotices(array $checkThis, $step = '')
    {
        $notice = Validator::param('notice')->isString()->getValue();
        if (null !== $notice) {
            return $notice;
        }
        $noticeList = [
            //'is_finished' => ['call' => 'isFinished']
        ];
        $checkList = \array_intersect_key($noticeList, array_flip($checkThis));
        return $this->getCheckListRedirect($checkList, $step, 'notice');
    }

    public function hasSuccesses(array $checkThis, $step = '')
    {
        $success = Validator::param('success')->isString()->getValue();
        if (null !== $success) {
            return $success;
        }
        $successList = [
            //'is_finished' => ['call' => 'isFinished']
        ];
        $checkList = \array_intersect_key($successList, array_flip($checkThis));
        return $this->getCheckListRedirect($checkList, $step, 'success');
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     * loop trough checklist to get status
     *
     * @return \BO\Slim\Render::redirect()
     */
    protected function getCheckListRedirect($checkList, $step, $type)
    {
        foreach ($checkList as $label => $item) {
            $check = (method_exists($this, $item['call'])) ? $this->{$item['call']}($step) : false;
            if ($check && isset($item['redirect'])) {
                $params = (\array_key_exists('params', $item)) ? $item['params'] : array();
                return \BO\Slim\Render::redirect($item['redirect'], $params, array($type => $label));
            }
        }
    }
}
