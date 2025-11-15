<?php

declare(strict_types=1);

namespace Test\Unit;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Test\TestCase;
use Veejay\Http\AbstractMessage;
use Veejay\Http\Request;
use Veejay\Http\Uri;

final class RequestTest extends TestCase
{
    const REQUEST_TARGET = '/request/target';
    const METHOD = 'PUT';

    public function testConstruct()
    {
        $request = $this->getRequest('patch', '/', '2.0', ['name' => ['val']], null);

        $this->assertSame('PATCH', $request->getMethod());
        $this->assertEquals(new Uri('/'), $request->getUri());
        $this->assertSame('2.0', $request->getProtocolVersion());
        $this->assertSame(['Name' => ['val']], $request->getHeaders());
        $this->assertEquals('', $request->getBody()->getContents());
    }

    public function testGetRequestTarget()
    {
        $request = $this->getRequest();
        $request->requestTarget = self::REQUEST_TARGET;
        $this->assertSame(self::REQUEST_TARGET, $request->getRequestTarget());
    }

    public function testWithRequestTarget()
    {
        $request = $this->getRequest();
        $new = $request->withRequestTarget(self::REQUEST_TARGET);
        $this->assertSame(self::REQUEST_TARGET, $new->getRequestTarget());
        $this->assertNotSame($request, $new);
    }

    public function testGetMethod()
    {
        $request = $this->getRequest();
        $request->method = self::METHOD;
        $this->assertSame(self::METHOD, $request->getMethod());
    }

    public function testWithMethod()
    {
        $request = $this->getRequest();

        $new = $request->withMethod(self::METHOD);
        $this->assertSame(self::METHOD, $new->getMethod());
        $this->assertNotSame($request, $new);

        $new = $request->withMethod('post'); // Lower case
        $this->assertSame('POST', $new->getMethod()); // Upper case
        $this->assertNotSame($request, $new);
    }

    public function testGetUri()
    {
        $request = $this->getRequest();
        $request->uri = new Uri;
        $this->assertSame($request->uri, $request->getUri());
    }

    public function testWithUri()
    {
        $uri = new Uri;
        $request = $this->getRequest();
        $new = $request->withUri($uri);
        $this->assertSame($uri, $new->getUri());
        $this->assertNotSame($request, $new);
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param string $protocolVersion
     * @param array $headers
     * @param StreamInterface|null $body
     * @return Request
     */
    protected function getRequest(
        string $method = 'GET',
        string|UriInterface $uri = '',
        string $protocolVersion = AbstractMessage::DEFAULT_PROTOCOL_VERSION,
        array $headers = [],
        ?StreamInterface $body = null,
    ) {
        return new class($method, $uri, $protocolVersion, $headers, $body) extends Request
        {
            public string $requestTarget;
            public string $method;
            public UriInterface $uri;
        };
    }
}
