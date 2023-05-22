<?php

namespace Tests\SolaPhp\Http\Psr7;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use SolaPhp\Http\Psr7\Message;
use SolaPhp\Http\Psr7\Stream;

class MessageTest extends TestCase
{
    protected Message $message;
    protected Stream $stream;
    protected string $testFilePath;

    public function setUp(): void
    {
        $m = new Message();
        $this->message = $m->withHeader('X-Foo', 'Bar');

        $this->testFilePath = dirname(__FILE__) . '/../../../fixtures/test.txt';
        $file = fopen($this->testFilePath, 'a+');
        $this->stream = new Stream($file);
    }

    public function testGetProtocolVersion()
    {
        $this->assertEquals('1.1', $this->message->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $newM = $this->message->withProtocolVersion('2.0');

        $this->assertEquals('2.0', $newM->getProtocolVersion());
    }

    public function testGetHeaders()
    {
        $headers = $this->message->getHeaders();

        $this->assertEquals('Bar', $headers['x-foo'][0]);
    }

    public function testHasHeader()
    {
        $this->assertTrue($this->message->hasHeader('x-foo'));
        $this->assertFalse($this->message->hasHeader('x-bar'));
    }

    public function testGetHeader()
    {
        $this->assertEquals(['Bar'], $this->message->getHeader('x-foo'));
        $this->assertEmpty($this->message->getHeader('x-bar'));
    }

    public function testGetHeaderLine()
    {
        $newM = $this->message->withHeader('X-Foo', ['Bar', 'Tar']);

        $this->assertEquals('Bar,Tar', $newM->getHeaderLine('x-foo'));
    }

    public function testWithHeader()
    {
        $this->assertEquals(['Bar'], $this->message->getHeader('x-foo'));
    }

    public function testWithHeaderInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header name can not be empty');
        $this->message->withHeader('', '');
    }

    public function testWithoutHeader()
    {
        $newM = $this->message->withoutHeader('x-foo');

        $this->assertEmpty($newM->getHeader('x-foo'));
    }

    public function testWithBody()
    {
        $newM = $this->message->withBody($this->stream);
        $this->assertEquals('hello sola', $newM->getBody()->getContents());
    }

    public function testGetBodyNotInitialized()
    {
        $body = $this->message->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
    }
}
