<?php

namespace BO\Slim\Middleware\Session;

/**
 * Check if human
 */
class SessionHuman extends SessionContainer
{
    const MAX_RELOAD = 10;

    const MAX_TIME = 1800;

    const MIN_TIME = 3;

    public function writeVerifySession($request, $origin = '')
    {
        $clientIp = $request->getAttribute('ip_address');
        $this->set('client', 1, 'human');
        $this->set('ts', time(), 'human');
        if (! $this->isOrigin('captcha')) {
            $this->set('origin', $origin, 'human');
        }
        $this->set('remoteAddress', $clientIp, 'human');
    }

    public function writeBotSession($origin = '')
    {
        $this->set('client', 0, 'human');
        $this->set('ts', 0, 'human');
        $this->set('origin', $origin, 'human');
    }

    /**
     * @SuppressWarnings(Complexity)
     * @return Boolean
     */
    public function redirectOnSuspicion($request, $requiredSteps = array(), $referer = false)
    {
        $path = $request->getUri()->getPath();
        if (! $this->isOrigin('captcha')) {
            foreach ($requiredSteps as $stepName) {
                if (!$this->hasStep($stepName)) {
                    \App::$log->notice(
                        "[Human " . session_id() . "] Missing step $stepName on " . $path . " (referer: " . $referer . ")"
                    );
                    $this->writeRedirectCaptcha($path, $stepName);
                    return true;
                }
                if ($this->hasStepMaxReload($stepName)) {
                    \App::$log->notice(
                        "[Human " . session_id() . "] Exceeded max reload for step $stepName on " . $path
                    );
                    $this->writeRedirectCaptcha($path, ($referer) ? $referer : end($requiredSteps));
                    return true;
                }
            }
            $clientIpAddress = $request->getAttribute('ip_address');
            if (!$this->has('remoteAddress', 'human') || $clientIpAddress != $this->get('remoteAddress', 'human')) {
                \App::$log->error("[Human " . session_id() . "] Missing remote address " . $clientIpAddress);
                $this->writeRedirectCaptcha($path, $referer);
                return true;
            }
        }
        if (!$this->isVerified()) {
            \App::$log->error(
                "[Human " . session_id() . "] Missing session on " . $path . " (referer: " . $referer . ")"
            );
            $this->writeRedirectCaptcha($path, $referer);
            return true;
        }
        $this->writeRedirectCaptcha($path, ($referer) ? $referer : end($requiredSteps));
        return false;
    }

    public function isOverAged()
    {
        if (!$this->has('ts', 'human') || time() > ($this->get('ts', 'human') + self::MAX_TIME)) {
            return true;
        }
        return false;
    }

    public function isUnderAged()
    {
        if (!$this->has('ts', 'human') || time() < ($this->get('ts', 'human') + self::MIN_TIME)) {
            return true;
        }
        return false;
    }

    /**
     *
     *
     * @return array
     */
    public function addStep($stepName)
    {
        if (!$this->has('step', 'human')) {
            $this->set('step', array(), 'human');
        }
        if (!array_key_exists($stepName, $this->get('step', 'human'))) {
            $stepCount = 1;
        } else {
            $stepCount = $this->get('step', 'human')[$stepName] + 1;
        }
        $step = $this->get('step', 'human');
        $step[$stepName] = $stepCount;
        $this->setGroup(array('human' => array('step' => $step)));
    }

    /**
     * check if has steps
     *
     * @return boolean
     */
    public function hasStep($stepName)
    {
        if ($this->has('step', 'human') && array_key_exists($stepName, $this->get('step', 'human'))) {
            return true;
        }
        return false;
    }

    /**
     * check if has steps
     *
     * @return boolean
     */
    public function hasStepMaxReload($stepName)
    {
        if (
            $this->has('step', 'human') &&
            array_key_exists($stepName, $this->get('step', 'human')) &&
            $this->get('step', 'human')[$stepName] > self::MAX_RELOAD
        ) {
            return true;
        }
        return false;
    }

    public function isVerified()
    {
        if ($this->has('client', 'human') && $this->get('client', 'human')) {
            return true;
        }
        return false;
    }

    /**
     * check if is origin
     *
     * @return boolean
     */
    protected function isOrigin($originName)
    {
        if ($this->has('origin', 'human') && $originName == $this->get('origin', 'human')) {
            return true;
        }
        return false;
    }

    /**
     *
     *
     * @return self
     */
    protected function writeRedirectCaptcha($path, $referer = false)
    {
        if (false === $referer) {
            $referer = basename($path);
        }
        $referer = array('human' => array('referer' => $referer));
        $this->setGroup($referer);
    }
}
