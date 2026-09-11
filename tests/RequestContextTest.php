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

$ctx = RequestContext::current();
$ctx->setHeaders(['X-Tenant-Id' => 'acme-corp']);
$returned = $ctx->getHeaders();
$returned['X-Tenant-Id'] = 'CORRUPTED';
assert_same('acme-corp', $ctx->getHeaders()['X-Tenant-Id'], 'getHeaders must return a copy');

$ctx->setHeaders(['X-Tenant-Id' => 'tenant-alpha', 'X-Request' => 'first']);
$ctx->setHeaders(['X-Tenant-Id' => 'tenant-beta', 'X-Request' => 'second']);
assert_same(
    ['X-Tenant-Id' => 'tenant-beta', 'X-Request' => 'second'],
    $ctx->getHeaders(),
    'setHeaders must replace the entire array'
);

$ctx->setHeaders([]);
assert_same([], $ctx->getHeaders(), 'headers must be empty after reset');

fwrite(STDOUT, "All tests passed\n");
