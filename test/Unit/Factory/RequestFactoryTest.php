<?php

declare(strict_types=1);

namespace Test\Unit\Factory;

use Test\TestCase;
use Veejay\Http\Factory\RequestFactory;
use Veejay\Http\Uri;

final class RequestFactoryTest extends TestCase
{
    public function testCreateRequest()
    {
        $factory = new RequestFactory;
        $request = $factory->createRequest('post', '/');

        $this->assertSame('POST', $request->getMethod());
        $this->assertEquals(new Uri('/'), $request->getUri());
    }
}
