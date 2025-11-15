<?php

declare(strict_types=1);

namespace Test\Unit;

use Test\TestCase;
use Veejay\Http\Uri;

final class UriTest extends TestCase
{
    protected const MATCHING = [
        0b00000000 => '',
        0b00000001 => '#fragment',
        0b00000010 => '?a=1',
        0b00000011 => '?a=1#fragment',
        0b00000100 => '/index.php',
        0b00000101 => '/index.php#fragment',
        0b00000110 => '/index.php?a=1',
        0b00000111 => '/index.php?a=1#fragment',
        0b00001000 => '',
        0b00001001 => '#fragment',
        0b00001010 => '?a=1',
        0b00001011 => '?a=1#fragment',
        0b00001100 => '/index.php',
        0b00001101 => '/index.php#fragment',
        0b00001110 => '/index.php?a=1',
        0b00001111 => '/index.php?a=1#fragment',
        0b00010000 => '//domain.ru',
        0b00010001 => '//domain.ru#fragment',
        0b00010010 => '//domain.ru?a=1',
        0b00010011 => '//domain.ru?a=1#fragment',
        0b00010100 => '//domain.ru/index.php',
        0b00010101 => '//domain.ru/index.php#fragment',
        0b00010110 => '//domain.ru/index.php?a=1',
        0b00010111 => '//domain.ru/index.php?a=1#fragment',
        0b00011000 => '//domain.ru:80',
        0b00011001 => '//domain.ru:80#fragment',
        0b00011010 => '//domain.ru:80?a=1',
        0b00011011 => '//domain.ru:80?a=1#fragment',
        0b00011100 => '//domain.ru:80/index.php',
        0b00011101 => '//domain.ru:80/index.php#fragment',
        0b00011110 => '//domain.ru:80/index.php?a=1',
        0b00011111 => '//domain.ru:80/index.php?a=1#fragment',
        0b00100000 => '',
        0b00100001 => '#fragment',
        0b00100010 => '?a=1',
        0b00100011 => '?a=1#fragment',
        0b00100100 => '/index.php',
        0b00100101 => '/index.php#fragment',
        0b00100110 => '/index.php?a=1',
        0b00100111 => '/index.php?a=1#fragment',
        0b00101000 => '',
        0b00101001 => '#fragment',
        0b00101010 => '?a=1',
        0b00101011 => '?a=1#fragment',
        0b00101100 => '/index.php',
        0b00101101 => '/index.php#fragment',
        0b00101110 => '/index.php?a=1',
        0b00101111 => '/index.php?a=1#fragment',
        0b00110000 => '//:pass@domain.ru',
        0b00110001 => '//:pass@domain.ru#fragment',
        0b00110010 => '//:pass@domain.ru?a=1',
        0b00110011 => '//:pass@domain.ru?a=1#fragment',
        0b00110100 => '//:pass@domain.ru/index.php',
        0b00110101 => '//:pass@domain.ru/index.php#fragment',
        0b00110110 => '//:pass@domain.ru/index.php?a=1',
        0b00110111 => '//:pass@domain.ru/index.php?a=1#fragment',
        0b00111000 => '//:pass@domain.ru:80',
        0b00111001 => '//:pass@domain.ru:80#fragment',
        0b00111010 => '//:pass@domain.ru:80?a=1',
        0b00111011 => '//:pass@domain.ru:80?a=1#fragment',
        0b00111100 => '//:pass@domain.ru:80/index.php',
        0b00111101 => '//:pass@domain.ru:80/index.php#fragment',
        0b00111110 => '//:pass@domain.ru:80/index.php?a=1',
        0b00111111 => '//:pass@domain.ru:80/index.php?a=1#fragment',
        0b01000000 => '',
        0b01000001 => '#fragment',
        0b01000010 => '?a=1',
        0b01000011 => '?a=1#fragment',
        0b01000100 => '/index.php',
        0b01000101 => '/index.php#fragment',
        0b01000110 => '/index.php?a=1',
        0b01000111 => '/index.php?a=1#fragment',
        0b01001000 => '',
        0b01001001 => '#fragment',
        0b01001010 => '?a=1',
        0b01001011 => '?a=1#fragment',
        0b01001100 => '/index.php',
        0b01001101 => '/index.php#fragment',
        0b01001110 => '/index.php?a=1',
        0b01001111 => '/index.php?a=1#fragment',
        0b01010000 => '//user@domain.ru',
        0b01010001 => '//user@domain.ru#fragment',
        0b01010010 => '//user@domain.ru?a=1',
        0b01010011 => '//user@domain.ru?a=1#fragment',
        0b01010100 => '//user@domain.ru/index.php',
        0b01010101 => '//user@domain.ru/index.php#fragment',
        0b01010110 => '//user@domain.ru/index.php?a=1',
        0b01010111 => '//user@domain.ru/index.php?a=1#fragment',
        0b01011000 => '//user@domain.ru:80',
        0b01011001 => '//user@domain.ru:80#fragment',
        0b01011010 => '//user@domain.ru:80?a=1',
        0b01011011 => '//user@domain.ru:80?a=1#fragment',
        0b01011100 => '//user@domain.ru:80/index.php',
        0b01011101 => '//user@domain.ru:80/index.php#fragment',
        0b01011110 => '//user@domain.ru:80/index.php?a=1',
        0b01011111 => '//user@domain.ru:80/index.php?a=1#fragment',
        0b01100000 => '',
        0b01100001 => '#fragment',
        0b01100010 => '?a=1',
        0b01100011 => '?a=1#fragment',
        0b01100100 => '/index.php',
        0b01100101 => '/index.php#fragment',
        0b01100110 => '/index.php?a=1',
        0b01100111 => '/index.php?a=1#fragment',
        0b01101000 => '',
        0b01101001 => '#fragment',
        0b01101010 => '?a=1',
        0b01101011 => '?a=1#fragment',
        0b01101100 => '/index.php',
        0b01101101 => '/index.php#fragment',
        0b01101110 => '/index.php?a=1',
        0b01101111 => '/index.php?a=1#fragment',
        0b01110000 => '//user:pass@domain.ru',
        0b01110001 => '//user:pass@domain.ru#fragment',
        0b01110010 => '//user:pass@domain.ru?a=1',
        0b01110011 => '//user:pass@domain.ru?a=1#fragment',
        0b01110100 => '//user:pass@domain.ru/index.php',
        0b01110101 => '//user:pass@domain.ru/index.php#fragment',
        0b01110110 => '//user:pass@domain.ru/index.php?a=1',
        0b01110111 => '//user:pass@domain.ru/index.php?a=1#fragment',
        0b01111000 => '//user:pass@domain.ru:80',
        0b01111001 => '//user:pass@domain.ru:80#fragment',
        0b01111010 => '//user:pass@domain.ru:80?a=1',
        0b01111011 => '//user:pass@domain.ru:80?a=1#fragment',
        0b01111100 => '//user:pass@domain.ru:80/index.php',
        0b01111101 => '//user:pass@domain.ru:80/index.php#fragment',
        0b01111110 => '//user:pass@domain.ru:80/index.php?a=1',
        0b01111111 => '//user:pass@domain.ru:80/index.php?a=1#fragment',
        0b10000000 => 'https:',
        0b10000001 => 'https:#fragment',
        0b10000010 => 'https:?a=1',
        0b10000011 => 'https:?a=1#fragment',
        0b10000100 => 'https:/index.php',
        0b10000101 => 'https:/index.php#fragment',
        0b10000110 => 'https:/index.php?a=1',
        0b10000111 => 'https:/index.php?a=1#fragment',
        0b10001000 => 'https:',
        0b10001001 => 'https:#fragment',
        0b10001010 => 'https:?a=1',
        0b10001011 => 'https:?a=1#fragment',
        0b10001100 => 'https:/index.php',
        0b10001101 => 'https:/index.php#fragment',
        0b10001110 => 'https:/index.php?a=1',
        0b10001111 => 'https:/index.php?a=1#fragment',
        0b10010000 => 'https://domain.ru',
        0b10010001 => 'https://domain.ru#fragment',
        0b10010010 => 'https://domain.ru?a=1',
        0b10010011 => 'https://domain.ru?a=1#fragment',
        0b10010100 => 'https://domain.ru/index.php',
        0b10010101 => 'https://domain.ru/index.php#fragment',
        0b10010110 => 'https://domain.ru/index.php?a=1',
        0b10010111 => 'https://domain.ru/index.php?a=1#fragment',
        0b10011000 => 'https://domain.ru:80',
        0b10011001 => 'https://domain.ru:80#fragment',
        0b10011010 => 'https://domain.ru:80?a=1',
        0b10011011 => 'https://domain.ru:80?a=1#fragment',
        0b10011100 => 'https://domain.ru:80/index.php',
        0b10011101 => 'https://domain.ru:80/index.php#fragment',
        0b10011110 => 'https://domain.ru:80/index.php?a=1',
        0b10011111 => 'https://domain.ru:80/index.php?a=1#fragment',
        0b10100000 => 'https:',
        0b10100001 => 'https:#fragment',
        0b10100010 => 'https:?a=1',
        0b10100011 => 'https:?a=1#fragment',
        0b10100100 => 'https:/index.php',
        0b10100101 => 'https:/index.php#fragment',
        0b10100110 => 'https:/index.php?a=1',
        0b10100111 => 'https:/index.php?a=1#fragment',
        0b10101000 => 'https:',
        0b10101001 => 'https:#fragment',
        0b10101010 => 'https:?a=1',
        0b10101011 => 'https:?a=1#fragment',
        0b10101100 => 'https:/index.php',
        0b10101101 => 'https:/index.php#fragment',
        0b10101110 => 'https:/index.php?a=1',
        0b10101111 => 'https:/index.php?a=1#fragment',
        0b10110000 => 'https://:pass@domain.ru',
        0b10110001 => 'https://:pass@domain.ru#fragment',
        0b10110010 => 'https://:pass@domain.ru?a=1',
        0b10110011 => 'https://:pass@domain.ru?a=1#fragment',
        0b10110100 => 'https://:pass@domain.ru/index.php',
        0b10110101 => 'https://:pass@domain.ru/index.php#fragment',
        0b10110110 => 'https://:pass@domain.ru/index.php?a=1',
        0b10110111 => 'https://:pass@domain.ru/index.php?a=1#fragment',
        0b10111000 => 'https://:pass@domain.ru:80',
        0b10111001 => 'https://:pass@domain.ru:80#fragment',
        0b10111010 => 'https://:pass@domain.ru:80?a=1',
        0b10111011 => 'https://:pass@domain.ru:80?a=1#fragment',
        0b10111100 => 'https://:pass@domain.ru:80/index.php',
        0b10111101 => 'https://:pass@domain.ru:80/index.php#fragment',
        0b10111110 => 'https://:pass@domain.ru:80/index.php?a=1',
        0b10111111 => 'https://:pass@domain.ru:80/index.php?a=1#fragment',
        0b11000000 => 'https:',
        0b11000001 => 'https:#fragment',
        0b11000010 => 'https:?a=1',
        0b11000011 => 'https:?a=1#fragment',
        0b11000100 => 'https:/index.php',
        0b11000101 => 'https:/index.php#fragment',
        0b11000110 => 'https:/index.php?a=1',
        0b11000111 => 'https:/index.php?a=1#fragment',
        0b11001000 => 'https:',
        0b11001001 => 'https:#fragment',
        0b11001010 => 'https:?a=1',
        0b11001011 => 'https:?a=1#fragment',
        0b11001100 => 'https:/index.php',
        0b11001101 => 'https:/index.php#fragment',
        0b11001110 => 'https:/index.php?a=1',
        0b11001111 => 'https:/index.php?a=1#fragment',
        0b11010000 => 'https://user@domain.ru',
        0b11010001 => 'https://user@domain.ru#fragment',
        0b11010010 => 'https://user@domain.ru?a=1',
        0b11010011 => 'https://user@domain.ru?a=1#fragment',
        0b11010100 => 'https://user@domain.ru/index.php',
        0b11010101 => 'https://user@domain.ru/index.php#fragment',
        0b11010110 => 'https://user@domain.ru/index.php?a=1',
        0b11010111 => 'https://user@domain.ru/index.php?a=1#fragment',
        0b11011000 => 'https://user@domain.ru:80',
        0b11011001 => 'https://user@domain.ru:80#fragment',
        0b11011010 => 'https://user@domain.ru:80?a=1',
        0b11011011 => 'https://user@domain.ru:80?a=1#fragment',
        0b11011100 => 'https://user@domain.ru:80/index.php',
        0b11011101 => 'https://user@domain.ru:80/index.php#fragment',
        0b11011110 => 'https://user@domain.ru:80/index.php?a=1',
        0b11011111 => 'https://user@domain.ru:80/index.php?a=1#fragment',
        0b11100000 => 'https:',
        0b11100001 => 'https:#fragment',
        0b11100010 => 'https:?a=1',
        0b11100011 => 'https:?a=1#fragment',
        0b11100100 => 'https:/index.php',
        0b11100101 => 'https:/index.php#fragment',
        0b11100110 => 'https:/index.php?a=1',
        0b11100111 => 'https:/index.php?a=1#fragment',
        0b11101000 => 'https:',
        0b11101001 => 'https:#fragment',
        0b11101010 => 'https:?a=1',
        0b11101011 => 'https:?a=1#fragment',
        0b11101100 => 'https:/index.php',
        0b11101101 => 'https:/index.php#fragment',
        0b11101110 => 'https:/index.php?a=1',
        0b11101111 => 'https:/index.php?a=1#fragment',
        0b11110000 => 'https://user:pass@domain.ru',
        0b11110001 => 'https://user:pass@domain.ru#fragment',
        0b11110010 => 'https://user:pass@domain.ru?a=1',
        0b11110011 => 'https://user:pass@domain.ru?a=1#fragment',
        0b11110100 => 'https://user:pass@domain.ru/index.php',
        0b11110101 => 'https://user:pass@domain.ru/index.php#fragment',
        0b11110110 => 'https://user:pass@domain.ru/index.php?a=1',
        0b11110111 => 'https://user:pass@domain.ru/index.php?a=1#fragment',
        0b11111000 => 'https://user:pass@domain.ru:80',
        0b11111001 => 'https://user:pass@domain.ru:80#fragment',
        0b11111010 => 'https://user:pass@domain.ru:80?a=1',
        0b11111011 => 'https://user:pass@domain.ru:80?a=1#fragment',
        0b11111100 => 'https://user:pass@domain.ru:80/index.php',
        0b11111101 => 'https://user:pass@domain.ru:80/index.php#fragment',
        0b11111110 => 'https://user:pass@domain.ru:80/index.php?a=1',
        0b11111111 => 'https://user:pass@domain.ru:80/index.php?a=1#fragment',
    ];

    public function testConstruct()
    {
        foreach (self::MATCHING as $address) {
            $uri = new Uri($address);
            $bitmask = $this->getBitmaskByUri($uri);
            $actual = self::MATCHING[$bitmask];
            $this->assertEquals($address, $actual);
        }
    }

    public function testToString()
    {
        foreach (self::MATCHING as $bitmask => $expected) {
            $actual = $this->getUriByBitmask($bitmask);
            $this->assertSame($expected, (string)$actual);
        }

        // If the path is starting with more than one "/" and no authority is present,
        // the starting slashes MUST be reduced to one.
        $uri = $this->getUri();

        $uri->path = '///index.php';
        $this->assertSame('/index.php', (string)$uri);

        $uri->host = 'domain.ru';
        $this->assertSame('//domain.ru///index.php', (string)$uri);
    }

    public function testGetScheme()
    {
        $uri = $this->getUri();
        $uri->scheme = 'https';
        $this->assertSame($uri->scheme, $uri->getScheme());
    }

    public function testGetAuthority()
    {
        $uri = $this->getUri();

        $items = [
            0b111 => 'user@domain.ru:1234',
            0b110 => 'user@domain.ru',
            0b101 => '',
            0b100 => '',
            0b011 => 'domain.ru:1234',
            0b010 => 'domain.ru',
            0b001 => '',
            0b000 => '',
        ];

        foreach ($items as $bitmask => $expected) {
            $uri->user = $bitmask & 0b100 ? 'user' : '';
            $uri->host = $bitmask & 0b010 ? 'domain.ru' : '';
            $uri->port = $bitmask & 0b001 ? 1234 : null;
            $this->assertSame($expected, $uri->getAuthority());
        }
    }

    public function testGetUserInfo()
    {
        $uri = $this->getUri();

        $uri->user = 'user';
        $uri->password = 'pass';
        $this->assertSame('user:pass', $uri->getUserInfo());

        $uri->user = 'user';
        $uri->password = null;
        $this->assertSame('user', $uri->getUserInfo());

        $uri->user = '';
        $uri->password = 'pass';
        $this->assertSame(':pass', $uri->getUserInfo());

        $uri->user = '';
        $uri->password = null;
        $this->assertSame('', $uri->getUserInfo());

        $uri->user = 'user';
        $uri->password = '';
        $this->assertSame('user:', $uri->getUserInfo());

        $uri->user = '';
        $uri->password = '';
        $this->assertSame(':', $uri->getUserInfo());
    }

    public function testGetUser()
    {
        $uri = $this->getUri();
        $uri->user = 'user';
        $this->assertSame($uri->user, $uri->getUser());
    }

    public function testGetPassword()
    {
        $uri = $this->getUri();
        $uri->password = 'pass';
        $this->assertSame($uri->password, $uri->getPassword());
    }

    public function testGetHost()
    {
        $uri = $this->getUri();
        $uri->host = 'domain.ru';
        $this->assertSame($uri->host, $uri->getHost());
    }

    public function testGetPort()
    {
        $uri = $this->getUri();
        $uri->port = 1234;
        $this->assertSame($uri->port, $uri->getPort());

        $uri->port = null;
        $this->assertNull($uri->getPort());
    }

    public function testGetPath()
    {
        $uri = $this->getUri();
        $uri->path = '/any/path';
        $this->assertSame($uri->path, $uri->getPath());
    }

    public function testGetQuery()
    {
        $uri = $this->getUri();
        $uri->query = '/any/path';
        $this->assertSame($uri->query, $uri->getQuery());
    }

    public function testGetFragment()
    {
        $uri = $this->getUri();
        $uri->fragment = 'fragment';
        $this->assertSame($uri->fragment, $uri->getFragment());
    }

    public function testWithScheme()
    {
        $uri = new Uri;

        $new = $uri->withScheme('http');
        $this->assertSame('http', $new->getScheme());

        $new = $uri->withScheme('HTTPS');
        $this->assertSame('https', $new->getScheme());

        $new = $uri->withScheme('abcd2');
        $this->assertSame('abcd2', $new->getScheme());

        $exception = $this->catchException(function () use ($uri) {
            $uri->withScheme('2abcd');
        });
        $this->assertNotNull($exception);

        $exception = $this->catchException(function () use ($uri) {
            $uri->withScheme('http://');
        });
        $this->assertNotNull($exception);

        $exception = $this->catchException(function () use ($uri) {
            $uri->withScheme('абвгд');
        });
        $this->assertNotNull($exception);
    }

    public function testWithUserInfo()
    {
        $uri = new Uri;

        $items = [
            ['user', 'pass', 'user:pass'],
            ['юзер', 'пасс',  rawurlencode('юзер') . ':' . rawurlencode('пасс')],
            ['User',  null,  'User'],
            ['user', '',     'user:'],
            ['',     'pass', ':pass'],
            ['',     '',     ':'],
            ['',      null,  ''],
        ];

        foreach ($items as $item) {
            $new = $uri->withUserInfo($item[0], $item[1]);
            $this->assertSame($item[2], $new->getUserInfo());
        }
    }

    public function testWithHost()
    {
        $uri = new Uri;

        $new = $uri->withHost('domAiN.Ru');
        $this->assertSame('domain.ru', $new->getHost());

        $new = $uri->withHost('домен.рф');
        $this->assertSame('xn--d1acufc.xn--p1ai', $new->getHost());

        $new = $uri->withHost('');
        $this->assertSame('', $new->getHost());
    }

    public function testWithPort()
    {
        $uri = new Uri;

        $new = $uri->withPort(0);
        $this->assertSame(0, $new->getPort());

        $new = $uri->withPort(65535);
        $this->assertSame(65535, $new->getPort());

        $new = $uri->withPort(null);
        $this->assertNull($new->getPort());

        $exception = $this->catchException(function () use ($uri) {
            $uri->withPort(-1);
        });
        $this->assertNotNull($exception);

        $exception = $this->catchException(function () use ($uri) {
            $uri->withPort(65536);
        });
        $this->assertNotNull($exception);
    }

    public function testWithPath()
    {
        $uri = new Uri;

        $new = $uri->withPath('/path/to');
        $this->assertSame('/path/to', $new->getPath());

        $new = $uri->withPath('path/to');
        $this->assertSame('path/to', $new->getPath());

        $new = $uri->withPath("/!$&'()*+,;=-_.~:@/");
        $this->assertSame("/!$&'()*+,;=-_.~:@/", $new->getPath());

        $new = $uri->withPath('/^?[]{}<>ЫэЯ');
        $this->assertSame('/%5E%3F%5B%5D%7B%7D%3C%3E%D0%AB%D1%8D%D0%AF', $new->getPath());
    }

    public function testWithQuery()
    {
        $uri = new Uri;

        $new = $uri->withQuery('A=1&b=Test');
        $this->assertSame('A=1&b=Test', $new->getQuery());

        $new = $uri->withQuery('?a=1&b=test');
        $this->assertSame('a=1&b=test', $new->getQuery());

        $new = $uri->withQuery("a=!$&'()*+,;=-_.~:@/?");
        $this->assertSame("a=!$&'()*+,;=-_.~:@/?", $new->getQuery());

        $new = $uri->withQuery('a=^[]{}<>ЫэЯ');
        $this->assertSame('a=%5E%5B%5D%7B%7D%3C%3E%D0%AB%D1%8D%D0%AF', $new->getQuery());
    }

    public function testWithFragment()
    {
        $uri = new Uri;

        $new = $uri->withFragment('Abc123');
        $this->assertSame('Abc123', $new->getFragment());

        $new = $uri->withFragment('#abc123');
        $this->assertSame('abc123', $new->getFragment());

        $new = $uri->withFragment("!$&'()*+,;=-_.~:@/?");
        $this->assertSame("!$&'()*+,;=-_.~:@/?", $new->getFragment());

        $new = $uri->withFragment('^[]{}<>ЫэЯ');
        $this->assertSame('%5E%5B%5D%7B%7D%3C%3E%D0%AB%D1%8D%D0%AF', $new->getFragment());
    }

    public function testImmutable()
    {
        $uri = new Uri;

        $new = $uri->withScheme('https');
        $this->assertNotSame($uri, $new);

        $new = $uri->withUserInfo('user', 'pass');
        $this->assertNotSame($uri, $new);

        $new = $uri->withHost('domain.ru');
        $this->assertNotSame($uri, $new);

        $new = $uri->withPort(80);
        $this->assertNotSame($uri, $new);

        $new = $uri->withPath('/index.php');
        $this->assertNotSame($uri, $new);

        $new = $uri->withQuery('a=1');
        $this->assertNotSame($uri, $new);

        $new = $uri->withFragment('fragment');
        $this->assertNotSame($uri, $new);
    }



    /**
     * @param string $uri
     * @return Uri
     */
    protected function getUri(string $uri = '')
    {
        return new class($uri) extends Uri
        {
            public string $scheme = '';
            public string $user = '';
            public ?string $password = null;
            public string $host = '';
            public ?int $port = null;
            public string $path = '';
            public string $query = '';
            public string $fragment = '';
        };
    }

    /**
     * Create an Uri object with default values according to a bitmask.
     * @param int $bitmask
     * @return Uri
     */
    protected function getUriByBitmask(int $bitmask): Uri
    {
        $uri = $this->getUri();

        $uri->scheme =   $bitmask & 0b10000000 ? 'https' : $uri->scheme;
        $uri->user =     $bitmask & 0b01000000 ? 'user' : $uri->user;
        $uri->password = $bitmask & 0b00100000 ? 'pass' : $uri->password;
        $uri->host =     $bitmask & 0b00010000 ? 'domain.ru' : $uri->host;
        $uri->port =     $bitmask & 0b00001000 ? 80 : $uri->port;
        $uri->path =     $bitmask & 0b00000100 ? '/index.php' : $uri->path;
        $uri->query =    $bitmask & 0b00000010 ? 'a=1' : $uri->query;
        $uri->fragment = $bitmask & 0b00000001 ? 'fragment' : $uri->fragment;

        return $uri;
    }

    /**
     * Create a bitmask using an Uri object.
     * @param Uri $uri
     * @return int
     */
    protected function getBitmaskByUri(Uri $uri): int
    {
        $bitmask = 0b00000000;

        $bitmask |= '' === $uri->getScheme()     ? 0b0 : 0b10000000;
        $bitmask |= '' === $uri->getUser()       ? 0b0 : 0b01000000;
        $bitmask |= null === $uri->getPassword() ? 0b0 : 0b00100000;
        $bitmask |= '' === $uri->getHost()       ? 0b0 : 0b00010000;
        $bitmask |= null === $uri->getPort()     ? 0b0 : 0b00001000;
        $bitmask |= '' === $uri->getPath()       ? 0b0 : 0b00000100;
        $bitmask |= '' === $uri->getQuery()      ? 0b0 : 0b00000010;
        $bitmask |= '' === $uri->getFragment()   ? 0b0 : 0b00000001;

        return $bitmask;
    }
}
