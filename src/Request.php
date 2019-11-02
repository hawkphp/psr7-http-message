<?php declare(strict_types=1);

namespace Hawk\Psr7;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class Request
 * @package Hawk\Psr7
 *
 * @link https://github.com/hawkphp/psr7-http-message GitHub main page
 */
class Request extends Message implements ServerRequestInterface
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $cookies;

    /**
     * @var array
     */
    protected $serverParams;

    /**
     * @var string
     */
    protected $requestTarget;

    /**
     * @var array
     */
    protected $queryParams;

    /**
     * @var null|array|object
     */
    protected $parsedBody;

    /**
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles;

    /**
     * @var array
     */
    private $validMethods = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'OPTIONS',
        'PATCH',
        'DELETE',
    ];

    /**
     * Request constructor.
     * @param string $method
     * @param UriInterface $uri
     * @param Headers $headers
     * @param array $serverParams
     * @param StreamInterface $body
     */
    public function __construct(
        $method,
        $uri,
        $headers,
        array $serverParams,
        StreamInterface $body
    ) {
        $this->method = $this->filterMethod($method);
        $this->uri = $uri;
        $this->headers = $headers;
        $this->serverParams = $serverParams;
        $this->attributes = [];
        $this->body = $body;

        if (isset($serverParams['SERVER_PROTOCOL'])) {
            $this->protocol = str_replace('HTTP/', '', $serverParams['SERVER_PROTOCOL']);
        }

        if (!$this->headers->hasHeader('Host') || $this->uri->getHost() !== '') {
            $this->headers->setHeader('Host', $this->uri->getHost());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        if ($this->uri === null) {
            return '/';
        }

        $target = $this->uri->getPath();
        $target = '/' . ltrim($target, '/');

        if ($query = $this->uri->getQuery()) {
            $target .= '?' . $query;
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }


    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $this->filterMethod($method);

        return $clone;
    }

    /**
     * @param string $method
     * @return string
     */
    protected function filterMethod($method)
    {
        $method = strtoupper($method);

        if (!is_string($method) || $method === '') {
            throw new \InvalidArgumentException('Method must be a non-empty string.');
        }

        if (!in_array($method, $this->validMethods, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported HTTP method "%s" provided', $method));
        }

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost && $uri->getHost() !== '') {
            $clone->headers->setHeader('Host', $uri->getHost());
            return $clone;
        }

        if (($uri->getHost() !== '' && !$this->hasHeader('Host') || $this->getHeaderLine('Host') === '')) {
            $clone->headers->setHeader('Host', $uri->getHost());
            return $clone;
        }

        return $clone;
    }


    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }
}
