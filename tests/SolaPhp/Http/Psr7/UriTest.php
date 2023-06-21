<?php

namespace Tests\SolaPhp\Http\Psr7;

use SolaPhp\Http\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    protected string $sampleUrl;
    protected Uri $uri;

    public function setUp(): void
    {
        $this->sampleUrl = 'http://username:password@hostname:9090/path?arg=value#anchor';
        $this->uri = new Uri($this->sampleUrl);
    }

    public function testNewUri()
    {
        $this->assertEquals($this->uri->getHost(), 'hostname');
        $this->assertEquals($this->uri->getPort(), 9090);
        $this->assertEquals($this->uri->getUserInfo(), 'username:password');
        $this->assertEquals($this->uri->getQuery(), 'arg=value');
        $this->assertEquals($this->uri->getFragment(), 'anchor');
        $this->assertEquals($this->uri->getScheme(), 'http');
        $this->assertEquals($this->uri->getPath(), '/path');
        $this->assertEquals($this->uri->getAuthority(), 'username:password@hostname:9090');
        $this->assertEquals($this->uri->__toString(), $this->sampleUrl);
    }

    public function testAnotherUriValid()
    {
        $uri2 = new Uri('http://hostname:9090/path?arg=value#anchor');
        $this->assertEquals($uri2->getHost(), 'hostname');
        $this->assertEquals($uri2->getPort(), 9090);
        $this->assertEquals($uri2->getQuery(), 'arg=value');
        $this->assertEquals($uri2->getUserInfo(), '');
    }

    public function testInvalidUri()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse URI: ////');
        new Uri('////');
    }

    public function testWithPath()
    {
        $uri2 = $this->uri->withPath('/path2');
        $this->assertEquals($uri2->getPath(), '/path2');
    }

    public function testWithPathInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Path');
        $this->uri->withPath('path2');
    }

    public function testWithScheme()
    {
        $uri2 = $this->uri->withScheme('https');
        $this->assertEquals($uri2->getScheme(), 'https');
    }

    public function testWithUserInfo()
    {
        $uri2 = $this->uri->withUserInfo('user', 'pass');
        $this->assertEquals($uri2->getUserInfo(), 'user:pass');
    }

    public function testWithPort()
    {
        $uri2 = $this->uri->withPort(8080);
        $this->assertEquals($uri2->getPort(), 8080);
    }

    public function testDefaultPort()
    {
        $uri2 = new Uri('http://hostname/path');
        $this->assertEquals($uri2->getPort(), 80);
    }

    public function testWithPortInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Port 65536 . Port should be between 0 and 65535');
        $this->uri->withPort(65536);
    }

    public function testWithQuery()
    {
        $uri2 = $this->uri->withQuery('arg2=value2');
        $this->assertEquals($uri2->getQuery(), 'arg2=value2');
    }

    public function testWithFragment()
    {
        $uri2 = $this->uri->withFragment('anchor2');
        $this->assertEquals($uri2->getFragment(), 'anchor2');
    }

    public function testWithHost()
    {
        $uri2 = $this->uri->withHost('host2');
        $this->assertEquals($uri2->getHost(), 'host2');
    }

    public function testWithHostInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Hostname');
        $this->uri->withHost('хозяин'); // russion for host
    }
}
