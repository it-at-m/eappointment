<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class Changelog extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \psr\http\message\requestinterface $request,
        \psr\http\message\responseinterface $response,
        array $args
    ) {
        return \BO\Slim\Render::withHtml(
            $response,
            'page/changelog.twig',
            array(
                'title' => 'Changelog',
                'menuActive' => 'changelog'
            )
        );
    }
}
