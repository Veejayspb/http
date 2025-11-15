<?php

declare(strict_types=1);

namespace Test\Unit;

use Psr\Http\Message\StreamInterface;
use Test\TestCase;
use Veejay\Http\Response;

final class ResponseTest extends TestCase
{
    protected const PROTOCOL = '1.1';
    protected const HEADERS = [];
    protected const BODY = null;
    protected const REASON_PHRASE = 'Default reason phrase';

    public function testConstruct()
    {
        $response = $this->getResponse(500, self::REASON_PHRASE);
        $this->assertSame(500, $response->statusCode);
        $this->assertSame(self::REASON_PHRASE, $response->reasonPhrase);

        $response = $this->getResponse(400);
        $this->assertSame(400, $response->statusCode);
        $this->assertSame(Response::getDefaultReasonPhrase(400), $response->reasonPhrase);

        $exception = $this->catchException(function () {
            $this->getResponse(1000);
        });
        $this->assertNotNull($exception);
    }

    public function testGetStatusCode()
    {
        $response = $this->getResponse();

        foreach (Response::REASON_PHRASES as $code => $reasonPhrase) {
            $response->statusCode = $code;
            $this->assertSame($code, $response->getStatusCode());
        }
    }

    public function testWithStatusDefault()
    {
        $response = $this->getResponse();

        foreach (Response::REASON_PHRASES as $code => $reasonPhrase) {
            $new = $response->withStatus($code);

            $this->assertSame($code, $new->getStatusCode());
            $this->assertSame(Response::REASON_PHRASES[$code], $new->getReasonPhrase());
            $this->assertNotSame($response, $new);
        }
    }

    public function testWithStatusSpecified()
    {
        $code = 500;
        $response = $this->getResponse();
        $new = $response->withStatus($code, self::REASON_PHRASE);

        $this->assertSame($code, $new->getStatusCode());
        $this->assertSame(self::REASON_PHRASE, $new->getReasonPhrase());
        $this->assertNotSame($response, $new);
    }

    public function testWithStatusInvalid()
    {
        $exception = $this->catchException(function () {
            $this->getResponse(0);
        });
        $this->assertNotNull($exception);

        $exception = $this->catchException(function () {
            $this->getResponse(600);
        });
        $this->assertNotNull($exception);
    }

    public function testGetReasonPhrase()
    {
        $response = $this->getResponse();

        for ($i = 0; $i <= 10; $i++) {
            $response->reasonPhrase = md5(strval($i)); // Any string
            $actual = $response->getReasonPhrase();
            $this->assertSame($response->reasonPhrase, $actual);
        }
    }

    public function testGetDefaultReasonPhrase()
    {
        foreach (Response::REASON_PHRASES as $code => $expected) {
            $actual = Response::getDefaultReasonPhrase($code);
            $this->assertSame($expected, $actual);
        }

        $actual = Response::getDefaultReasonPhrase(0, self::REASON_PHRASE);
        $this->assertSame(self::REASON_PHRASE, $actual);
    }

    /**
     * @return Response
     */
    protected function getResponse(
        int $statusCode = 200,
        string $reasonPhrase = '',
        string $protocolVersion = self::PROTOCOL,
        array $headers = self::HEADERS,
        ?StreamInterface $body = self::BODY,
    ) {
        return new class($statusCode, $reasonPhrase, $protocolVersion, $headers, $body) extends Response
        {
            public int $statusCode = 200;
            public string $reasonPhrase = '';
        };
    }
}
