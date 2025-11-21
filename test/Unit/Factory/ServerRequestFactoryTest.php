<?php

declare(strict_types=1);

namespace Test\Unit\Factory;

use Test\TestCase;
use Veejay\Http\Factory\ServerRequestFactory;
use Veejay\Http\Uri;

final class ServerRequestFactoryTest extends TestCase
{
    protected const METHOD = 'PATCH';
    protected const URI = '/';
    protected const SERVER_PARAMS = ['param_one' => 'value_one'];

    public function testCreateServerRequest()
    {
        $factory = new ServerRequestFactory;
        $serverRequest = $factory->createServerRequest(self::METHOD, self::URI, self::SERVER_PARAMS);

        $this->assertSame(self::METHOD, $serverRequest->getMethod());
        $this->assertEquals(new Uri(self::URI), $serverRequest->getUri());
        $this->assertSame(self::SERVER_PARAMS, $serverRequest->getServerParams());
    }
}
