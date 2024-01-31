<?php

namespace BO\Zmsticketprinter\Tests;

class OldRedirectTest extends Base
{

    protected $classname = "RedirectOld";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $response = $this->render([ ], [
            'auswahlstandortid' => array(363,141,142),
            'auswahlclusterid' => array(109),
            'OID' => 71
        ], [ ]);
        $this->assertRedirect($response, '/?ticketprinter%5Bbuttonlist%5D=s363%2Cs141%2Cs142%2Cc109');
    }
}
