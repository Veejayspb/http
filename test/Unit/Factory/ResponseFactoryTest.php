<?php

declare(strict_types=1);

namespace Test\Unit\Factory;

use Test\TestCase;
use Veejay\Http\Factory\ResponseFactory;

final class ResponseFactoryTest extends TestCase
{
    public function testCreateResponse()
    {
        $factory = new ResponseFactory;
        $response = $factory->createResponse(404, 'Four zero four');

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Four zero four', $response->getReasonPhrase());
    }
}
