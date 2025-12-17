<?php

declare(strict_types=1);

namespace Test\Unit;

use Psr\Http\Message\StreamInterface;
use Test\TestCase;
use Veejay\Http\AbstractMessage;
use Veejay\Http\Stream;

final class AbstractMessageTest extends TestCase
{
    protected const PROTOCOL_VERSION = '22';
    protected const HEADER_NAME = 'Name';
    protected const HEADER_NAME_ACTUAL = 'name';
    protected const HEADER_VALUE = ['v1', 'v2'];
    protected const HEADERS = [self::HEADER_NAME => self::HEADER_VALUE];
    protected const HEADERS_ACTUAL = [self::HEADER_NAME_ACTUAL => self::HEADER_VALUE];

    public function testGetProtocolVersion()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION);

        $actual = $message->getProtocolVersion();
        $this->assertSame(self::PROTOCOL_VERSION, $actual);
    }

    public function testWithProtocolVersion()
    {
        $message = $this->getMessage();
        $new = $message->withProtocolVersion(self::PROTOCOL_VERSION);

        $actual = $new->getProtocolVersion();
        $this->assertSame(self::PROTOCOL_VERSION, $actual);
    }

    public function testGetHeaders()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS);

        $this->assertSame(self::HEADERS, $message->getHeaders());
    }

    public function testHasHeader()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS_ACTUAL);

        $this->assertTrue($message->hasHeader('Name'));
        $this->assertTrue($message->hasHeader('name'));
        $this->assertFalse($message->hasHeader('Undefined'));
    }

    public function testGetHeader()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS_ACTUAL);

        $actual = $message->getHeader(self::HEADER_NAME);
        $this->assertSame(self::HEADER_VALUE, $actual);

        $expected = [];
        $actual = $message->getHeader('Undefined');
        $this->assertSame($expected, $actual);
    }

    public function testGetHeaderLine()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS_ACTUAL);

        $expected = 'v1, v2';
        $actual = $message->getHeaderLine(self::HEADER_NAME);
        $this->assertSame($expected, $actual);

        $expected = '';
        $actual = $message->getHeaderLine('Undefined');
        $this->assertSame($expected, $actual);
    }

    public function testWithHeader()
    {
        $message = $this->getMessage();
        $new = $message->withHeader(self::HEADER_NAME, self::HEADER_VALUE);

        $this->assertSame(self::HEADERS, $new->getHeaders());
    }

    public function testWithAddedHeader()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS_ACTUAL);
        $new = $message->withAddedHeader(self::HEADER_NAME, ['v3']);

        $expected = ['v1', 'v2' ,'v3'];
        $this->assertSame($expected, $new->getHeader(self::HEADER_NAME));
    }

    public function testWithoutHeader()
    {
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS_ACTUAL);

        $this->assertSame(self::HEADERS, $message->getHeaders());

        $new = $message->withoutHeader(self::HEADER_NAME);
        $this->assertSame([], $new->getHeaders());

        $new = $message->withoutHeader('Undefined');
        $this->assertSame(self::HEADERS, $new->getHeaders());
    }

    public function testGetBody()
    {
        $stream = $this->getStream();
        $message = $this->getMessage(self::PROTOCOL_VERSION, self::HEADERS, $stream);

        $actual = $message->getBody();
        $this->assertSame($stream, $actual);
    }

    public function testWithBody()
    {
        $stream = $this->getStream();
        $message = $this->getMessage();
        $new = $message->withBody($stream);

        $actual = $new->getBody();
        $this->assertSame($stream, $actual);
    }

    /**
     * @param string $protocolVersion
     * @param array $headers
     * @param StreamInterface|null $body
     * @return AbstractMessage
     */
    protected function getMessage(
        string $protocolVersion = '1.1',
        array $headers = [],
        ?StreamInterface $body = null,
    ) {
        $message = new class extends AbstractMessage
        {
            public string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION;
            public array $headers = [];
            public StreamInterface $body;
        };

        $message->protocolVersion = $protocolVersion;
        $message->headers = $headers;
        $message->body = $body ?? $this->getStream();

        return $message;
    }

    /**
     * @return Stream
     */
    protected function getStream()
    {
        $resource = $this->getResource();
        return new Stream($resource);
    }

    /**
     * @return resource
     */
    protected function getResource()
    {
        return fopen('php://temp', 'r+');
    }
}
