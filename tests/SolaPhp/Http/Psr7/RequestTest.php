<?php

namespace Tests\SolaPhp\Http\Psr7;

use SolaPhp\Http\Psr7\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    protected Request $request;

    public function setUp(): void
    {
        $this->request = new Request('GET', '/');
    }

    public function testConstructor()
    {
        $this->assertEquals('/', $this->request->getUri()->getPath());
        $this->assertEquals('GET', $this->request->getMethod());
    }

    public function testWithRequestTarget()
    {
        $request2 = $this->request->withRequestTarget('/foo');
        $this->assertEquals('/foo', (string) $request2->getRequestTarget());
    }

    public function testWithMethod()
    {
        $request2 = $this->request->withMethod('POST');
        $this->assertEquals('POST', $request2->getMethod());
    }

    public function testInvalidMethod()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP method FOO');
        $request2 = $this->request->withMethod('FOO');
    }
}
