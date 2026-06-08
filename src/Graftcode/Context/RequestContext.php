<?php

declare(strict_types=1);

namespace Graftcode\Context;

class RequestContext
{
    private static ?RequestContext $current = null;

    /** @var array<string, ?string> */
    private array $headers = [];

    public static function current(): RequestContext
    {
        if (self::$current === null) {
            self::$current = new RequestContext();
        }

        return self::$current;
    }

    /**
     * @return array<string, ?string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string, ?string> $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
