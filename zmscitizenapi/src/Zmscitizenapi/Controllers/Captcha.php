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
            
            if (is_array($result) && !empty($result['errors'])) {
                $statusCode = ErrorMessages::getHighestStatusCode($result['errors']);
                return $this->createJsonResponse($response, $result, $statusCode);
            }

            return $result instanceof FriendlyCaptcha
            ? $this->createJsonResponse($response, $result->getCaptchaDetails(), 200)
            : $this->createJsonResponse(
                $response, 
                ErrorMessages::get('invalidRequest'), 
                ErrorMessages::get('invalidRequest')['statusCode']
            );
            
        } catch (\Exception $e) {
            return $this->createJsonResponse(
                $response,
                ['errors' => [ErrorMessages::get('captchaVerificationError')]],
                500
            );
        }
    }

    private function getCaptchaDetails(): FriendlyCaptcha
    {
        return new FriendlyCaptcha();
    }
}