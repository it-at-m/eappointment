<?php

namespace BO\Slim\Middleware\Session;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Check if human
 */
class SessionHuman extends SessionContainer
{
    const int MAX_RELOAD = 10;

    const int MAX_TIME = 1800;

    const int MIN_TIME = 3;

    /** @psalm-api */
    public function writeVerifySession(ServerRequestInterface $request, string $origin = ''): void
    {
        $clientIp = $request->getAttribute('ip_address');
        $this->set('client', 1, 'human');
        $this->set('ts', time(), 'human');
        if (! $this->isOrigin('captcha')) {
            $this->set('origin', $origin, 'human');
        }
        $this->set('remoteAddress', $clientIp, 'human');
    }

    /** @psalm-api */
    public function writeBotSession(string $origin = ''): void
    {
        $this->set('client', 0, 'human');
        $this->set('ts', 0, 'human');
        $this->set('origin', $origin, 'human');
    }

    /**
     * @SuppressWarnings(Complexity)
     * @param string[] $requiredSteps
     * @psalm-api
     */
    public function redirectOnSuspicion(
        ServerRequestInterface $request,
        array $requiredSteps = [],
        string|false $referer = false
    ): bool {
        $path = $request->getUri()->getPath();
        $sessionId = session_id();
        $sessionId = $sessionId === false ? '' : $sessionId;
        $refererLabel = is_string($referer) ? $referer : '';
        if (! $this->isOrigin('captcha')) {
            foreach ($requiredSteps as $stepName) {
                if (!$this->hasStep($stepName)) {
                    \App::$log->notice(
                        "[Human " . $sessionId . "] Missing step $stepName on " . $path . " (referer: " . $refererLabel . ")"
                    );
                    $this->writeRedirectCaptcha($path, $stepName);
                    return true;
                }
                if ($this->hasStepMaxReload($stepName)) {
                    \App::$log->notice(
                        "[Human " . $sessionId . "] Exceeded max reload for step $stepName on " . $path
                    );
                    $this->writeRedirectCaptcha($path, ($referer !== false) ? $referer : end($requiredSteps));
                    return true;
                }
            }
            $clientIpAddress = $request->getAttribute('ip_address');
            if (!$this->has('remoteAddress', 'human') || $clientIpAddress != $this->get('remoteAddress', 'human')) {
                \App::$log->error("[Human " . $sessionId . "] Missing remote address " . $clientIpAddress);
                $this->writeRedirectCaptcha($path, $referer);
                return true;
            }
        }
        if (!$this->isVerified()) {
            \App::$log->error(
                "[Human " . $sessionId . "] Missing session on " . $path . " (referer: " . $refererLabel . ")"
            );
            $this->writeRedirectCaptcha($path, $referer);
            return true;
        }
        $this->writeRedirectCaptcha($path, ($referer !== false) ? $referer : end($requiredSteps));
        return false;
    }

    /** @psalm-api */
    public function isOverAged(): bool
    {
        if (!$this->has('ts', 'human') || time() > ($this->get('ts', 'human') + self::MAX_TIME)) {
            return true;
        }
        return false;
    }

    /** @psalm-api */
    public function isUnderAged(): bool
    {
        if (!$this->has('ts', 'human') || time() < ($this->get('ts', 'human') + self::MIN_TIME)) {
            return true;
        }
        return false;
    }

    /** @psalm-api */
    public function addStep(string $stepName): void
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

    public function hasStep(string $stepName): bool
    {
        if ($this->has('step', 'human') && array_key_exists($stepName, $this->get('step', 'human'))) {
            return true;
        }
        return false;
    }

    public function hasStepMaxReload(string $stepName): bool
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

    public function isVerified(): bool
    {
        if ($this->has('client', 'human') && $this->get('client', 'human')) {
            return true;
        }
        return false;
    }

    protected function isOrigin(string $originName): bool
    {
        if ($this->has('origin', 'human') && $originName == $this->get('origin', 'human')) {
            return true;
        }
        return false;
    }

    protected function writeRedirectCaptcha(string $path, string|false $referer = false): void
    {
        if (false === $referer) {
            $referer = basename($path);
        }
        $referer = array('human' => array('referer' => $referer));
        $this->setGroup($referer);
    }
}
