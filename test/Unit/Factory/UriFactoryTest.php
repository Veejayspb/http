<?php

declare(strict_types=1);

namespace Test\Unit\Factory;

use Test\TestCase;
use Veejay\Http\Factory\UriFactory;
use Veejay\Http\Uri;

final class UriFactoryTest extends TestCase
{
    public function testCreateUri()
    {
        $address = 'https://user:pass@domain.ru:80/index.php?a=1#fragment';

        $this->assertEquals(
            new Uri($address),
            (new UriFactory)->createUri($address)
        );
    }
}
