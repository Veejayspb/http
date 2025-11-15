<?php

declare(strict_types=1);

namespace Veejay\Http;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * Server parameters.
     * @var array
     */
    protected array $serverParams = [];

    /**
     * Cookies list.
     * @var array
     */
    protected array $cookieParams = [];

    /**
     * Query string arguments.
     * @var array
     */
    protected array $queryParams = [];

    /**
     * Normalized file upload data.
     * @var array
     */
    protected array $uploadedFiles = [];

    /**
     * Parameters provided in the request body.
     * @var array|object|null
     */
    protected array|object|null $parsedBody = null;

    /**
     * Attributes derived from the request.
     * @var array
     */
    protected array $attributes = [];

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param string $protocolVersion
     * @param array $headers
     * @param StreamInterface|null $body
     * @param array $serverParams
     * @param array $uploadedFiles
     * @param array $cookieParams
     * @param array $queryParams
     * @param array|object|null $parsedBody
     */
    public function __construct(
        string $method = 'GET',
        UriInterface|string $uri = '',
        string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION,
        array $headers = [],
        ?StreamInterface $body = null,
        array $serverParams = [],
        array $uploadedFiles = [],
        array $cookieParams = [],
        array $queryParams = [],
        array|object|null $parsedBody = null,
    ) {
        parent::__construct($method, $uri, $protocolVersion, $headers, $body);
        $this->serverParams = $serverParams;
        $this->setUploadedFiles($uploadedFiles);
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->parsedBody = $parsedBody;
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
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
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
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    /**
     * {@inheritdoc}
     * @return array|UploadedFileInterface[] $uploadedFiles
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     * @param array|UploadedFileInterface[] $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new = clone $this;
        $new->setUploadedFiles($uploadedFiles);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        if (!is_array($data) && !is_object($data) && !is_null($data)) {
            throw new InvalidArgumentException('Parsed body must be an array, an object, or null');
        }

        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
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
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute(string $name, mixed $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    /**
     * Validate and set an array of uploaded files .
     * @param array|UploadedFileInterface[] $uploadedFiles
     * @return void
     */
    protected function setUploadedFiles(array $uploadedFiles): void
    {
        $this->validateUploadedFiles($uploadedFiles);
        $this->uploadedFiles = $uploadedFiles;
    }

    /**
     * Uploaded files must be an array of UploadedFileInterface instances.
     * @param array $uploadedFiles
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
                continue;
            }

            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid item in uploaded files structure. "%s" is not an instance of "%s".',
                    is_object($file) ? get_class($file) : gettype($file),
                    UploadedFileInterface::class
                ));
            }
        }
    }
}
