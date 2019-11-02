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

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @var StreamFactory|StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var UriFactory|UriFactoryInterface
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
            throw new InvalidArgumentException('URI must either be string or instance of ' . UriInterface::class);
        }

        $body = $this->streamFactory->createStream();
        $headers = new Headers();

        if (!empty($serverParams)) {
            if (function_exists('getallheaders')) {
                $headersData = getallheaders();
                if (is_array($headersData) && count($headersData)) {
                    $headers->setHeaders($headersData);
                }
            }
        }

        return new Request($method, $uri, $headers, $serverParams, $body);
    }
}
