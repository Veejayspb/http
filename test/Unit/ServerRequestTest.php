<?php

declare(strict_types=1);

namespace Test\Unit;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use stdClass;
use Test\TestCase;
use Veejay\Http\AbstractMessage;
use Veejay\Http\ServerRequest;
use Veejay\Http\UploadedFile;

final class ServerRequestTest extends TestCase
{
    const SERVER_PARAMS = ['SERVER_PROTOCOL' => 'HTTP/1.0'];
    const COOKIE_PARAMS = ['key' => 'value'];
    const QUERY_PARAMS = ['param' => 'one'];
    const PARSED_BODY = ['name' => 'val'];
    const ATTRIBUTES = ['post_one' => '1'];

    public function testConstruct()
    {
        $uploadedFiles = [new UploadedFile(__FILE__)];

        $serverRequest = $this->getServerRequest(
            'GET',
            '/',
            AbstractMessage::DEFAULT_PROTOCOL_VERSION,
            [],
            null,
            self::SERVER_PARAMS,
            $uploadedFiles,
            self::COOKIE_PARAMS,
            self::QUERY_PARAMS,
            self::PARSED_BODY,
        );

        $this->assertSame(self::SERVER_PARAMS, $serverRequest->serverParams);
        $this->assertSame($uploadedFiles, $serverRequest->uploadedFiles);
        $this->assertSame(self::COOKIE_PARAMS, $serverRequest->cookieParams);
        $this->assertSame(self::QUERY_PARAMS, $serverRequest->queryParams);
        $this->assertSame(self::PARSED_BODY, $serverRequest->parsedBody);
    }

    public function testGetServerParams()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest->serverParams = self::SERVER_PARAMS;
        $actual = $serverRequest->getServerParams();
        $this->assertSame(self::SERVER_PARAMS, $actual);
    }

    public function testGetCookieParams()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest->cookieParams = self::COOKIE_PARAMS;
        $actual = $serverRequest->getCookieParams();
        $this->assertSame(self::COOKIE_PARAMS, $actual);
    }

    public function testWithCookieParams()
    {
        $serverRequest = $this->getServerRequest();
        $new = $serverRequest->withCookieParams(self::COOKIE_PARAMS);
        $this->assertSame(self::COOKIE_PARAMS, $new->getCookieParams());
        $this->assertNotSame($serverRequest, $new);
    }

    public function testGetQueryParams()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest->queryParams = self::QUERY_PARAMS;
        $actual = $serverRequest->getQueryParams();
        $this->assertSame(self::QUERY_PARAMS, $actual);
    }

    public function testWithQueryParams()
    {
        $serverRequest = $this->getServerRequest();
        $new = $serverRequest->withQueryParams(self::QUERY_PARAMS);
        $this->assertSame(self::QUERY_PARAMS, $new->getQueryParams());
        $this->assertNotSame($serverRequest, $new);
    }

    public function testGetUploadedFiles()
    {
        $file = new UploadedFile(__FILE__);
        $serverRequest = $this->getServerRequest();
        $serverRequest->uploadedFiles = [$file];
        $actual = $serverRequest->getUploadedFiles();
        $this->assertSame([$file], $actual);
    }

    public function testWithUploadedFiles()
    {
        $file = new UploadedFile(__FILE__);
        $serverRequest = $this->getServerRequest();
        $new = $serverRequest->withUploadedFiles([$file]);
        $this->assertSame([$file], $new->getUploadedFiles());
        $this->assertNotSame($serverRequest, $new);
    }

    public function testGetParsedBody()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest->parsedBody = self::PARSED_BODY;
        $actual = $serverRequest->getParsedBody();
        $this->assertSame(self::PARSED_BODY, $actual);
    }

    public function testWithParsedBody()
    {
        $serverRequest = $this->getServerRequest();

        $validItems = [
            [],
            new stdClass,
            null,
        ];

        foreach ($validItems as $item) {
            $new = $serverRequest->withParsedBody($item);
            $this->assertSame($item, $new->getParsedBody());
            $this->assertNotSame($serverRequest, $new);
        }

        $invalidItems = [
            1,
            2.2,
            true,
            'str',
        ];

        foreach ($invalidItems as $item) {
            $exception = $this->catchException(function () use ($serverRequest, $item) {
                $serverRequest->withParsedBody($item);
            });
            $this->assertNotNull($exception);
        }
    }

    public function testGetAttributes()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest->attributes = self::ATTRIBUTES;
        $actual = $serverRequest->getAttributes();
        $this->assertSame(self::ATTRIBUTES, $actual);
    }

    public function testGetAttribute()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest->attributes = self::ATTRIBUTES;

        // Attribute exists
        $actual = $serverRequest->getAttribute('post_one');
        $this->assertSame('1', $actual);

        // Default value
        $actual = $serverRequest->getAttribute('post_two', 'default');
        $this->assertSame('default', $actual);
    }

    public function testWithAttribute()
    {
        $name = 'post_two';
        $serverRequest = $this->getServerRequest();
        $new = $serverRequest->withAttribute($name, '2');
        $this->assertNotNull($new->getAttribute($name));
        $this->assertNotSame($serverRequest, $new);
    }

    public function testWithoutAttribute()
    {
        $serverRequest = $this->getServerRequest();
        $new = $serverRequest->withoutAttribute('post_one');
        $this->assertSame([], $new->getAttributes());
        $this->assertNotSame($serverRequest, $new);
    }

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
     * @return ServerRequest
     */
    protected function getServerRequest(
        string $method = 'GET',
        UriInterface|string $uri = '',
        string $protocolVersion = AbstractMessage::DEFAULT_PROTOCOL_VERSION,
        array $headers = [],
        ?StreamInterface $body = null,
        array $serverParams = [],
        array $uploadedFiles = [],
        array $cookieParams = [],
        array $queryParams = [],
        array|object|null $parsedBody = null,
    ) {
        return new class(
            $method,
            $uri,
            $protocolVersion,
            $headers,
            $body,
            $serverParams,
            $uploadedFiles,
            $cookieParams,
            $queryParams,
            $parsedBody,
        ) extends ServerRequest
        {
            public array $serverParams = [];
            public array $cookieParams = [];
            public array $queryParams = [];
            public array $uploadedFiles = [];
            public array|object|null $parsedBody = null;
            public array $attributes = [];
        };
    }
}
