<?php

declare(strict_types=1);

namespace Veejay\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * Default protocol version.
     */
    public const DEFAULT_PROTOCOL_VERSION = '1.1';

    /**
     * @var string
     * @example 1.0|1.1|2.0
     */
    protected string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION;

    /**
     * Headers list.
     * @var array
     */
    protected array $headers = [];

    /**
     * Data stream object.
     * @var StreamInterface|null
     */
    protected ?StreamInterface $body = null;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        $result = [];

        foreach ($this->headers as $name => $values) {
            $name = $this->normalizeHeaderName($name);
            $result[$name] = $values;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader(string $name): bool
    {
        $name = $this->denormalizeHeaderName($name);
        return array_key_exists($name, $this->headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader(string $name): array
    {
        $name = $this->denormalizeHeaderName($name);
        return $this->headers[$name] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine(string $name): string
    {
        $values = $this->getHeader($name);
        return implode(', ', $values);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader(string $name, mixed $value): MessageInterface
    {
        $name = $this->denormalizeHeaderName($name);

        $new = clone $this;
        $new->setHeader($name, (array)$value);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader(string $name, mixed $value): MessageInterface
    {
        $name = $this->denormalizeHeaderName($name);

        $new = clone $this;
        $header = $new->getHeader($name);
        $new->setHeader($name, array_merge($header, (array)$value));
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $name = $this->denormalizeHeaderName($name);

        $new = clone $this;
        unset($new->headers[$name]);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        if (is_null($this->body)) {
            $resource = fopen('php://temp', 'r+');
            $stream = new Stream($resource);
            $this->setBody($stream);
        }

        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->setBody($body);
        return $new;
    }

    /**
     * Set first char of every word to upper case.
     * @param string $name
     * @return string
     */
    protected function normalizeHeaderName(string $name): string
    {
        return ucwords($name, '-');
    }

    /**
     * Handle header name to make it storable.
     * @param string $name
     * @return string
     */
    protected function denormalizeHeaderName(string $name): string
    {
        return strtolower($name);
    }

    /**
     * Set header values.
     * @param string $name
     * @param array $values
     * @return void
     */
    protected function setHeader(string $name, array $values): void
    {
        $name = $this->denormalizeHeaderName($name);
        $this->headers[$name] = array_map('strval', $values);
    }

    /**
     * Set headers values.
     * @param array $headers
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $values) {
            $this->setHeader($name, (array)$values);
        }
    }

    /**
     * Set body value.
     * @param StreamInterface|null $body
     * @return void
     */
    protected function setBody(?StreamInterface $body = null): void
    {
        $this->body = $body;
    }
}
