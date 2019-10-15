<?php declare(strict_types=1);

namespace Hawk\Tests\Psr7;

use Hawk\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Class UriTest
 * @package Hawk\Tests\Psr7
 */
class UriTest extends TestCase
{
    /**
     *
     */
    const URL = "https://user:pass@example.com:81/foo/bar/foo2.html?a=b&b[]=1&b[]=2#fragment";

    /**
     * @return Uri
     */
    public function getUri()
    {
        return new Uri(self::URL);
    }

    /**
     *
     */
    public function tesScheme()
    {
        // https
        $this->assertEquals('https', $this->getUri()->getScheme());

        // http
        $uri = $this->getUri()->withScheme('http');
        $this->assertEquals('http', $uri->getScheme());

        // empty
        $uri = $this->getUri()->withScheme('');
        $this->assertEquals('', $uri->getScheme());

        // removes suffix
        $uri = $this->getUri()->withScheme('http://');
        $this->assertEquals('http', $uri->getScheme());

        $this->assertSame(self::URL, (string)$this->getUri());
    }

    /**
     *
     */
    public function testWithSchemeInvalid()
    {
        $this->getUri()->withScheme('test');

        // invalid type
        $this->getUri()->withScheme([]);
    }

    /**
     *
     */
    public function testGetAuthority()
    {
        $this->assertEquals('user:pass@example.com', $this->getUri()->getAuthority());
    }

    /**
     *
     */
    public function testWithUserInfoAndGetUserInfo()
    {
        $uri = $this->getUri()->withUserInfo('hawk', 'pass');

        $this->assertEquals('hawk:pass', $uri->getUserInfo());
    }

    /**
     *
     */
    public function testWithUserInfoClear()
    {
        $uri = $this->getUri()->withUserInfo('hawk', 'pass');
        $uri = $uri->withUserInfo('');
        $this->assertEquals('', $uri->getUserInfo());
    }

    /**
     *
     */
    public function testGetHost()
    {
        $this->assertEquals('example.com', $this->getUri()->getHost());
    }

    /**
     *
     */
    public function testWithHost()
    {
        $uri = $this->getUri()->withHost('example.com');
        $this->assertEquals('example.com', $uri->getHost());
    }

    /**
     *
     */
    public function testGetPort()
    {
        $uri = new Uri('https://www.example.com:8080');
        $this->assertEquals(8080, $uri->getPort());
    }

    /**
     *
     */
    public function testGetPortWithoutPort()
    {
        $uri = new Uri('example.com');
        $this->assertNull($uri->getPort());
    }

    /**
     *
     */
    public function testWithPort()
    {
        $uri = $this->getUri()->withPort(8080);
        $this->assertEquals(8080, $uri->getPort());
    }

    /**
     *
     */
    public function testWithPortNull()
    {
        $uri = $this->getUri()->withPort(null);
        $this->assertEquals(null, $uri->getPort());
    }

    /**
     *
     */
    public function testWithPortInvalid()
    {
        $this->getUri()->withPort(65699);
    }

    /**
     *
     */
    public function testWithPortInvalidString()
    {
        $this->getUri()->withPort('Port');
    }

    /**
     *
     */
    public function testGetPath()
    {
        $this->assertEquals('/foo/bar', $this->getUri()->getPath());
    }

    /**
     *
     */
    public function testWithPath()
    {
        $uri = $this->getUri()->withPath('/with/path');
        $this->assertEquals('/with/path', $uri->getPath());
    }

    /**
     *
     */
    public function testWithPathWithoutPrefix()
    {
        $uri = $this->getUri()->withPath('path');
        $this->assertEquals('path', $uri->getPath());
    }

    /**
     *
     */
    public function testWithPathEmptyValue()
    {
        $uri = $this->getUri()->withPath('');
        $this->assertEquals('', $uri->getPath());
    }

    /**
     *
     */
    public function testGetQuery()
    {
        $this->assertEquals('a=b', $this->getUri()->getQuery());
    }

    /**
     *
     */
    public function testWithQuery()
    {
        $uri = $this->getUri()->withQuery('c=d');
        $this->assertEquals('c=d', $uri->getQuery());
    }

    /**
     *
     */
    public function testWithQueryPrefixRemoves()
    {
        $uri = $this->getUri()->withQuery('?test=prefix');
        $this->assertEquals('test=prefix', $uri->getQuery());
    }

    /**
     *
     */
    public function testGetFragment()
    {
        $this->assertEquals('fragment', $this->getUri()->getFragment());
    }

    /**
     *
     */
    public function testWithFragment()
    {
        $uri = $this->getUri()->withFragment('fragment-with');
        $this->assertEquals('fragment-with', $uri->getFragment());
    }

    /**
     *
     */
    public function testWithFragmentPrefixRemoves()
    {
        $uri = $this->getUri()->withFragment('#other-fragment');
        $this->assertEquals('other-fragment', $uri->getFragment());
    }

    /**
     *
     */
    public function testWithFragmentEmpty()
    {
        $uri = $this->getUri()->withFragment('');
        $this->assertEquals('', $uri->getFragment());
    }

    /**
     *
     */
    public function testToString()
    {
        $uri = $this->getUri();
        $this->assertEquals(self::URL, (string)$uri);

        $uri = $uri->withPath('foo');
        $this->assertEquals(self::URL, (string)$uri);

        $uri = $uri->withPath('/foo');
        $this->assertEquals(self::URL, (string)$uri);
    }
}
