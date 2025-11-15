<?php

declare(strict_types=1);

namespace Veejay\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends AbstractMessage implements RequestInterface
{
    /**
     * Message's request target.
     * @var string
     */
    protected string $requestTarget;

    /**
     * HTTP method of the request.
     * @var string
     * @example GET|POST|PUT|PATCH|DELETE
     */
    protected string $method;

    /**
     * URI instance.
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * @param string $protocolVersion
     * @param array $headers
     * @param StreamInterface|null $body
     * @param string $method
     * @param string|UriInterface $uri
     */
    public function __construct(
        string $method = 'GET',
        string|UriInterface $uri = '',
        string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION,
        array $headers = [],
        ?StreamInterface $body = null,
    ) {
        $this->setMethod($method);
        $this->setUri($uri);
        $this->protocolVersion = $protocolVersion;
        $this->setHeaders($headers);
        $this->body = $body;
        $this->requestTarget = $this->buildRequestTarget($this->uri);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
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
    public function withMethod(string $method): RequestInterface
    {
        $new = clone $this;
        $new->setMethod($method);
        return $new;
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
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$new->hasHeader('host')) {
            $new->updateHostFromUri($uri);
        }

        // Refresh request target if was default
        if ($this->requestTarget == $this->buildRequestTarget($this->uri)) {
            $new->requestTarget = $this->buildRequestTarget($uri);
        }

        return $new;
    }

    /**
     * Set method name.
     * @param string $method
     * @return void
     */
    protected function setMethod(string $method): void
    {
        $this->method = strtoupper($method);
    }

    /**
     * Set uri.
     * @param string|UriInterface $uri
     * @return void
     */
    protected function setUri(string|UriInterface $uri): void
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        $this->uri = $uri;
    }

    /**
     * Use uri to build request target.
     * @param UriInterface $uri
     * @return string
     */
    private function buildRequestTarget(UriInterface $uri): string
    {
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $target = $path ?: '/';

        if ($query !== '') {
            $target .= '?' . $query;
        }

        return $target;
    }

    /**
     * Update host header using Uri.
     * @param UriInterface $uri
     * @return void
     */
    private function updateHostFromUri(UriInterface $uri): void
    {
        $host = $uri->getHost();

        if ($host === '') {
            return;
        }

        $port = $uri->getPort();

        if (!is_null($port)) {
            $host .= ':' . $port;
        }

        $this->setHeader('host', [$host]);
    }
}
