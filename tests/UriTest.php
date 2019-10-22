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
    public function getFactoryUri()
    {
        return new Uri(self::URL);
    }

    /**
     *
     */
    public function tesScheme()
    {
        // https
        $this->assertEquals('https', $this->getFactoryUri()->getScheme());

        // http
        $uri = $this->getFactoryUri()->withScheme('http');
        $this->assertEquals('http', $uri->getScheme());

        // empty
        $uri = $this->getFactoryUri()->withScheme('');
        $this->assertEquals('', $uri->getScheme());

        // removes suffix
        $uri = $this->getFactoryUri()->withScheme('http://');
        $this->assertEquals('http', $uri->getScheme());

        $this->assertSame(self::URL, (string)$this->getFactoryUri());
    }

    /**
     *
     */
    public function testWithSchemeInvalid()
    {
        $this->getFactoryUri()->withScheme('test');

        // invalid type
        $this->getFactoryUri()->withScheme([]);
    }

    /**
     *
     */
    public function testGetAuthority()
    {
        $this->assertEquals('user:pass@example.com', $this->getFactoryUri()->getAuthority());
    }

    /**
     *
     */
    public function testWithUserInfoAndGetUserInfo()
    {
        $uri = $this->getFactoryUri()->withUserInfo('hawk', 'pass');

        $this->assertEquals('hawk:pass', $uri->getUserInfo());
    }

    /**
     *
     */
    public function testWithUserInfoClear()
    {
        $uri = $this->getFactoryUri()->withUserInfo('hawk', 'pass');
        $uri = $uri->withUserInfo('');
        $this->assertEquals('', $uri->getUserInfo());
    }

    /**
     *
     */
    public function testGetHost()
    {
        $this->assertEquals('example.com', $this->getFactoryUri()->getHost());
    }

    /**
     *
     */
    public function testWithHost()
    {
        $uri = $this->getFactoryUri()->withHost('example.com');
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
        $uri = $this->getFactoryUri()->withPort(8080);
        $this->assertEquals(8080, $uri->getPort());
    }

    /**
     *
     */
    public function testWithPortNull()
    {
        $uri = $this->getFactoryUri()->withPort(null);
        $this->assertEquals(null, $uri->getPort());
    }

    /**
     *
     */
    public function testWithPortInvalid()
    {
        $this->getFactoryUri()->withPort(65699);
    }

    /**
     *
     */
    public function testWithPortInvalidString()
    {
        $this->getFactoryUri()->withPort('Port');
    }

    /**
     *
     */
    public function testGetPath()
    {
        $this->assertEquals('/foo/bar', $this->getFactoryUri()->getPath());
    }

    /**
     *
     */
    public function testWithPath()
    {
        $uri = $this->getFactoryUri()->withPath('/with/path');
        $this->assertEquals('/with/path', $uri->getPath());
    }

    /**
     *
     */
    public function testWithPathWithoutPrefix()
    {
        $uri = $this->getFactoryUri()->withPath('path');
        $this->assertEquals('path', $uri->getPath());
    }

    /**
     *
     */
    public function testWithPathEmptyValue()
    {
        $uri = $this->getFactoryUri()->withPath('');
        $this->assertEquals('', $uri->getPath());
    }

    /**
     *
     */
    public function testGetQuery()
    {
        $this->assertEquals('a=b', $this->getFactoryUri()->getQuery());
    }

    /**
     *
     */
    public function testWithQuery()
    {
        $uri = $this->getFactoryUri()->withQuery('c=d');
        $this->assertEquals('c=d', $uri->getQuery());
    }

    /**
     *
     */
    public function testWithQueryPrefixRemoves()
    {
        $uri = $this->getFactoryUri()->withQuery('?test=prefix');
        $this->assertEquals('test=prefix', $uri->getQuery());
    }

    /**
     *
     */
    public function testGetFragment()
    {
        $this->assertEquals('fragment', $this->getFactoryUri()->getFragment());
    }

    /**
     *
     */
    public function testWithFragment()
    {
        $uri = $this->getFactoryUri()->withFragment('fragment-with');
        $this->assertEquals('fragment-with', $uri->getFragment());
    }

    /**
     *
     */
    public function testWithFragmentPrefixRemoves()
    {
        $uri = $this->getFactoryUri()->withFragment('#other-fragment');
        $this->assertEquals('other-fragment', $uri->getFragment());
    }

    /**
     *
     */
    public function testWithFragmentEmpty()
    {
        $uri = $this->getFactoryUri()->withFragment('');
        $this->assertEquals('', $uri->getFragment());
    }

    /**
     *
     */
    public function testToString()
    {
        $uri = $this->getFactoryUri();
        $this->assertEquals(self::URL, (string)$uri);

        $uri = $uri->withPath('foo');
        //$this->assertEquals(self::URL, (string)$uri);

        $uri = $uri->withPath('/foo');
        //$this->assertEquals(self::URL, (string)$uri);
    }
}
