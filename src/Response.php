<?php

declare(strict_types=1);

namespace Veejay\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends AbstractMessage implements ResponseInterface
{
    /**
     * Matching of status codes and reason phrases.
     */
    public const REASON_PHRASES = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // Client Errors 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // Server Errors 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    protected const DEFAULT_REASON_PHRASE = 'Unknown';
    protected const STATUS_CODE_MIN = 100;
    protected const STATUS_CODE_MAX = 599;
    protected const DEFAULT_STATUS_CODE = 200;

    /**
     * Response status code.
     * @var int
     * @example 200|400|500
     */
    protected int $statusCode = self::DEFAULT_STATUS_CODE;

    /**
     * The reason phrase to use with the provided status code.
     * @var string
     */
    protected string $reasonPhrase = '';

    /**
     * @param string $protocolVersion
     * @param array $headers
     * @param StreamInterface|null $body
     * @param int $statusCode
     * @param string $reasonPhrase
     */
    public function __construct(
        int $statusCode = self::DEFAULT_STATUS_CODE,
        string $reasonPhrase = '',
        string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION,
        array $headers = [],
        ?StreamInterface $body = null,
    ) {
        $this->setStatus($statusCode, $reasonPhrase);
        $this->protocolVersion = $protocolVersion;
        $this->setHeaders($headers);
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->setStatus($code, $reasonPhrase);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Return default reason phrase for specific code.
     * @param int $code
     * @param string $default
     * @return string
     */
    public static function getDefaultReasonPhrase(int $code, string $default = self::DEFAULT_REASON_PHRASE): string
    {
        return self::REASON_PHRASES[$code] ?? $default;
    }

    /**
     * Set status code and reason phrase.
     * @param int $code
     * @param string $reasonPhrase
     * @return void
     * @throws InvalidArgumentException
     */
    protected function setStatus(int $code, string $reasonPhrase = ''): void
    {
        if ($code < self::STATUS_CODE_MIN || self::STATUS_CODE_MAX < $code) {
            throw new InvalidArgumentException(sprintf(
                'Response status code "%d" is not valid. It must be in %d..%d range.',
                $code,
                self::STATUS_CODE_MIN,
                self::STATUS_CODE_MAX
            ));
        }

        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase ?: self::getDefaultReasonPhrase($code);
    }
}
