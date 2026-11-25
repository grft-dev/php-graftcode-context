# graftcode-context

Request context for Graftcode invocations in PHP.

The Gateway injects request headers into `RequestContext`. Your handler should only **read** them.

```php
use Graftcode\Context\RequestContext;

$headers = RequestContext::current()->getHeaders();
$auth = $headers['authorization'] ?? null;
```

- Use `RequestContext::current()->getHeaders()` every time you need headers.
- The context is read-only for application code: there is no public way to set headers or replace the current context.
- `getHeaders()` returns a **copy**. Mutating that array does not change the live context.
- Header names arrive in **lowercase** (the Gateway lowercases them for HTTP/2 and WebSocket) and the lookup is case-sensitive: read `authorization`, not `Authorization`.
- The Gateway forwards all request headers on `/ws`, `/h2` and `/run` (only HTTP/2 pseudo-headers are skipped). A header sent more than once arrives as one value: `cookie` values joined with `"; "`, other headers with `", "`.
- Every call has its own headers; the host puts the previous headers back after the call.
- There is one context per process/worker. With one request per process (php-fpm, CLI) nothing else is needed. In a long-running process the host swaps the headers for every call and puts the previous ones back afterwards. Fibers and coroutines in one worker share the same context.

Full notes: [Request context and headers](../../docs/request-context.md)
