<?php

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Request;
use Hawk\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Class RequestTest
 * @package Hawk\Tests\Psr7
 */
class RequestTest extends TestCase
{
    public function testConstructUriAndMethod()
    {
        $request = new Request();
        $this->assertSame('', $request->getUri()->getPath());
        $this->assertEquals('GET', (string)$request->getMethod());
    }

    public function testConstructHeaders()
    {
        $request = new Request('/', 'POST', ['Accept' => 'Age']);
        $this->assertEquals(['Age'], $request->getHeader('Accept'));
    }

    public function testConstructWithBody()
    {
        $request = new Request('/', 'GET', [], 'test');
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('test', (string)$request->getBody());
    }

    public function testNullBody()
    {
        $request = new Request('/', 'GET', [], null);
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('', (string)$request->getBody());
    }

    public function testGetBody()
    {
        $request = new Request('/', 'GET', [], 'test');
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('test', (string)$request->getBody());
    }

    public function testWithUri()
    {
        $request = new Request('/');
        $uri = $request->getUri();
        $this->assertSame($uri, $request->getUri());

        $uri2 = new Uri('http://example.com');
        $request2 = $request->withUri($uri2);
        $this->assertNotSame($request, $request2);
        $this->assertSame($uri2, $request2->getUri());
        $this->assertSame($uri, $request->getUri());
    }

    public function testWithRequestTarget()
    {
        $request1 = new Request('/');
        $request2 = $request1->withRequestTarget('?');
        $this->assertEquals('?', $request2->getRequestTarget());
        $this->assertEquals('/', $request1->getRequestTarget());
    }

    public function testGetRequestTarget()
    {
        $request = new Request('http://example.com');
        $this->assertEquals('/', $request->getRequestTarget());

        $request = new Request('http://example.com/test?foo=bar');
        $this->assertEquals('/test?foo=bar', $request->getRequestTarget());

        $request = new Request('http://example.com?foo=bar');
        $this->assertEquals('/?foo=bar', $request->getRequestTarget());

        // request target does not allow spaces
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target provided; cannot contain whitespace');

        $request = new Request('/');
        $request->withRequestTarget('/test foo bar');

        $request = new Request();
        $this->assertEquals('/', $request->getRequestTarget());

        $request = new Request('http://example.com/test foo/');
        $this->assertEquals('/test%20foo/', $request->getRequestTarget());

        $r1 = new Request('http://example.com/test?foo=bar');
        $this->assertEquals('/test?foo=bar', $r1->getRequestTarget());


        $r1 = new Request('http://example.com/test?xyz', 'GET');
        $this->assertEquals('/test?xyz', $r1->getRequestTarget());
    }

    public function testHostIsAddedFirst()
    {
        $request = new Request('http://example.com/test?foo=bar', 'GET', ['Foo' => 'Bar']);
        $this->assertEquals(['Host' => ['example.com'], 'Foo' => ['Bar']], $request->getHeaders());
    }
}
