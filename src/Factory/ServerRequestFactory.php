<?php declare(strict_types=1);

namespace Hawk\Psr7\Factory;

use Hawk\Psr7\Headers;
use Hawk\Psr7\Request;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class ServerRequestFactory
 * @package Hawk\Psr7\Factory
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @var StreamFactory|StreamFactoryInterface|null
     */
    protected $streamFactory;

    /**
     * @var UriFactory|UriFactoryInterface|null
     */
    protected $uriFactory;

    /**
     * ServerRequestFactory constructor.
     * @param StreamFactoryInterface|null $streamFactory
     * @param UriFactoryInterface|null $uriFactory
     */
    public function __construct(StreamFactoryInterface $streamFactory = null, UriFactoryInterface $uriFactory = null)
    {
        if (!isset($streamFactory)) {
            $streamFactory = new StreamFactory();
        }

        if (!isset($uriFactory)) {
            $uriFactory = new UriFactory();
        }

        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException('URI must either be instance of UriInterface');
        }

        $body = $this->streamFactory->createStream();
        $headers = new Headers();
        $cookies = self::parseHeader($headers->getHeader('Cookie', []));

        if (!empty($serverParams)) {
            $headers = self::fromGlobals();
        }

        return new Request($method, $uri, $body, $headers, $serverParams, $cookies);
    }

    /**
     * @param $header
     * @return array
     */
    public static function parseHeader($header): array
    {
        if (is_array($header)) {
            $header = isset($header[0]) ? $header[0] : '';
        }

        if (!is_string($header)) {
            throw new InvalidArgumentException('Cannot parse Cookie data. Header value must be a string.');
        }

        $header = rtrim($header, "\r\n");
        $pcs = preg_split('@[;]\s*@', $header);
        $cookies = [];

        if (is_array($pcs)) {
            foreach ($pcs as $cookie) {
                $cookie = explode('=', $cookie, 2);
                if (count($cookie) === 2) {
                    $key = urldecode($cookie[0]);
                    $value = urldecode($cookie[1]);
                    if (!isset($cookies[$key])) {
                        $cookies[$key] = $value;
                    }
                }
            }
        }
        return $cookies;
    }

    /**
     * @return Request
     */
    public static function fromGlobals(): Request
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $uri = (new UriFactory())->createFromGlobals($_SERVER);

        $headers = Headers::createFromGlobals();
        $cookies = self::parseHeader($headers->getHeader('Cookie', []));

        // Cache the php://input stream as it cannot be re-read
        $cacheResource = fopen('php://temp', 'wb+');
        $cache = $cacheResource ? new Stream($cacheResource) : null;

        $body = (new StreamFactory())->createStreamFromFile('php://input', 'r', $cache);
        $uploadedFiles = UploadedFile::createFromGlobals($_SERVER);

        $request = new Request($method, $uri, $headers, $cookies, $_SERVER, $body, $uploadedFiles);
        $contentTypes = $request->getHeader('Content-Type') ?? [];

        $parsedContentType = '';
        foreach ($contentTypes as $contentType) {
            $fragments = explode(';', $contentType);
            $parsedContentType = current($fragments);
        }

        $contentTypesWithParsedBodies = ['application/x-www-form-urlencoded', 'multipart/form-data'];
        if ($method === 'POST' && in_array($parsedContentType, $contentTypesWithParsedBodies)) {
            return $request->withParsedBody($_POST);
        }

        return $request;
    }
}
