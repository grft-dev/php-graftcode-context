<?php

declare(strict_types=1);

namespace Graftcode\Context;

class RequestContext
{
    private static ?RequestContext $current = null;

    /** @var array<string, ?string> */
    private array $headers = [];

    /**
     * Context of the current invocation. PHP serves one request per process/worker at a time,
     * so a static is enough. (PHP has no fiber-local storage; Fibers share this context.)
     */
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
        // Arrays are values in PHP: returning one hands the caller a copy (copy-on-write).
        return $this->headers;
    }

    /**
     * Host only, called through reflection.
     *
     * @param array<string, ?string> $headers
     */
    private function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
