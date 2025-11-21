<?php

declare(strict_types=1);

namespace Veejay\Http\Factory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Veejay\Http\Response;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response($code, $reasonPhrase);
    }
}
