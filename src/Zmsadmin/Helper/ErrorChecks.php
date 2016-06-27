<?php

/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 *
 */
namespace BO\Zmsadmin\Helper;

class ErrorChecks extends ErrorBase
{
    public function handleOwner()
    {
        $error = $this->hasErrors(array(), 'owner');
        $notice = $this->hasNotices(array(), 'owner');
        $success = $this->hasSuccesses(array(), 'owner');

        if ($error instanceof \Slim\Http\Response) {
            return $error;
        } elseif ($notice instanceof \Slim\Http\Response) {
            return $notice;
        } elseif ($success instanceof \Slim\Http\Response) {
            return $success;
        } else {
            return array(
                'error' => $error,
                'notice' => $notice,
                'success' => $success
            );
        }
    }

    public function handleOwnerOverview()
    {
        $error = $this->hasErrors(array(), 'owner');
        $notice = $this->hasNotices(array(), 'owner');
        $success = $this->hasSuccesses(array(), 'owner');

        if ($error instanceof \Slim\Http\Response) {
            return $error;
        } elseif ($notice instanceof \Slim\Http\Response) {
            return $notice;
        } elseif ($success instanceof \Slim\Http\Response) {
            return $success;
        } else {
            return array(
                'error' => $error,
                'notice' => $notice,
                'success' => $success
            );
        }
    }

    public function handleDepartment()
    {
        $error = $this->hasErrors(array(), 'department');
        $notice = $this->hasNotices(array(), 'department');
        $success = $this->hasSuccesses(array(), 'department');

        if ($error instanceof \Slim\Http\Response) {
            return $error;
        } elseif ($notice instanceof \Slim\Http\Response) {
            return $notice;
        } elseif ($success instanceof \Slim\Http\Response) {
            return $success;
        } else {
            return array(
                'error' => $error,
                'notice' => $notice,
                'success' => $success
            );
        }
    }

    public function handleOrganisation()
    {
        $error = $this->hasErrors(array(), 'organisation');
        $notice = $this->hasNotices(array(), 'organisation');
        $success = $this->hasSuccesses(array(), 'organisation');

        if ($error instanceof \Slim\Http\Response) {
            return $error;
        } elseif ($notice instanceof \Slim\Http\Response) {
            return $notice;
        } elseif ($success instanceof \Slim\Http\Response) {
            return $success;
        } else {
            return array(
                'error' => $error,
                'notice' => $notice,
                'success' => $success
            );
        }
    }

    public function handleScope()
    {
        $error = $this->hasErrors(array(), 'scope');
        $notice = $this->hasNotices(array(), 'scope');
        $success = $this->hasSuccesses(array(), 'scope');

        if ($error instanceof \Slim\Http\Response) {
            return $error;
        } elseif ($notice instanceof \Slim\Http\Response) {
            return $notice;
        } elseif ($success instanceof \Slim\Http\Response) {
            return $success;
        } else {
            return array(
                'error' => $error,
                'notice' => $notice,
                'success' => $success
            );
        }
    }
}
