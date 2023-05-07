<?php

namespace Tests\SolaPhp\SolaPhp;

use PHPUnit\Framework\TestCase;
use SolaPhp\SolaPhp\Controller;

class ControllerTest extends TestCase
{
    public function testInstantiate()
    {
        $c = new Controller();
        $this->assertInstanceOf(Controller::class, $c);
    }
}
