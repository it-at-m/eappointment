<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Controllers;

use BO\Zmscitizenapi\BaseController;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Models\Captcha\FriendlyCaptcha;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Captcha extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $result = $this->getCaptchaDetails();
            
            if (!empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $this->createJsonResponse($response, $result, 200);
            
        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('captchaVerificationError')]],
                500
            );
        }
    }

    private function getCaptchaDetails(): array
    {
        $captcha = new FriendlyCaptcha();
        return $captcha->getCaptchaDetails();
    }
}