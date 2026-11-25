<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Graftcode\Context\RequestContext;

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual: ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

/** Sets headers the way the host does: setHeaders is private, so through reflection. */
function set_headers_as_host(RequestContext $context, array $headers): void
{
    $method = new ReflectionMethod(RequestContext::class, 'setHeaders');
    if (PHP_VERSION_ID < 80100) {
        $method->setAccessible(true);
    }
    $method->invoke($context, $headers);
}
assert_same(false, (new ReflectionMethod(RequestContext::class, 'setHeaders'))->isPublic(), 'setHeaders must not be public');

$ctx = RequestContext::current();
set_headers_as_host($ctx, ['X-Tenant-Id' => 'acme-corp']);
$returned = $ctx->getHeaders();
$returned['X-Tenant-Id'] = 'CORRUPTED';
assert_same('acme-corp', $ctx->getHeaders()['X-Tenant-Id'], 'getHeaders must return a copy');

set_headers_as_host($ctx, ['X-Tenant-Id' => 'tenant-alpha', 'X-Request' => 'first']);
set_headers_as_host($ctx, ['X-Tenant-Id' => 'tenant-beta', 'X-Request' => 'second']);
assert_same(
    ['X-Tenant-Id' => 'tenant-beta', 'X-Request' => 'second'],
    $ctx->getHeaders(),
    'setHeaders must replace the entire array'
);

set_headers_as_host($ctx, []);
assert_same([], $ctx->getHeaders(), 'headers must be empty after reset');

// Like the host: every call sets its headers on the current context. A service keeps the one it saw in the first call.
set_headers_as_host(RequestContext::current(), ['X-Tenant-Id' => 'tenant-1']);
$kept = RequestContext::current();
set_headers_as_host(RequestContext::current(), []);
set_headers_as_host(RequestContext::current(), ['X-Tenant-Id' => 'tenant-2']);
assert_same('tenant-2', $kept->getHeaders()['X-Tenant-Id'], 'a context kept from an earlier call must show the headers of the call reading it');
set_headers_as_host(RequestContext::current(), []);

fwrite(STDOUT, "All tests passed\n");
