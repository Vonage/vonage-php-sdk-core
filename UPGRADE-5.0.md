# Upgrade Guide: 4.x → 5.0

This document describes every breaking change in 5.0.0 and explains how to update your code.

## Before You Start

### 1. PHP 8.2 is now the minimum

PHP 8.1 is no longer supported. Ensure your application runs on PHP 8.2, 8.3, 8.4, or 8.5.

### 2. Remove `vonage/nexmo-bridge`

The `vonage/nexmo-bridge` compatibility package (`\Nexmo` → `\Vonage` aliases) was announced as a
temporary migration bridge several years ago. It has been removed entirely. Any `\Nexmo\*` class
references in your code will cause fatal errors after upgrading. You must have already migrated all
`\Nexmo\*` references to their `\Vonage\*` equivalents before upgrading to 5.0.

### 3. Enable `E_USER_DEPRECATED` warnings during development

The 4.x releases emit `E_USER_DEPRECATED` runtime warnings wherever a deprecated API is used.
Run your application under a development configuration that surfaces these warnings (e.g.
`error_reporting(E_ALL)`) and fix every deprecation before upgrading to 5.0.0.

---

## Removed APIs

### Meetings API

The Vonage Meetings API has been sunset. The `Vonage\Meetings` namespace and
`Vonage\Meetings\Client` have been removed from the SDK.

### ProactiveConnect API

The Vonage ProactiveConnect API has been sunset. The `Vonage\ProactiveConnect` namespace and
`Vonage\ProactiveConnect\Client` have been removed from the SDK.

### SimSwap API

The SimSwap API has been removed from the SDK. Refer to the
[Vonage Network APIs documentation](https://developer.vonage.com/en/network-apis/overview) for
the replacement. The `Vonage\SimSwap` namespace is deleted.

### Number Verification API

The older Number Verification client has been removed. The `Vonage\NumberVerification` namespace
is deleted.

---

## Credentials

### `Vonage\Client\Credentials\Gnp` removed

The `Gnp` credential class has been removed along with all GNP-specific auth handlers:

- `Vonage\Client\Credentials\Handler\GnpKeypairHandler`
- `Vonage\Client\Credentials\Handler\SimSwapGnpHandler`
- `Vonage\Client\Credentials\Handler\NumberVerificationGnpHandler`

### Deprecated HTTP-layer credential handlers removed

The following handler classes that were deprecated in 4.x have been deleted:

- `Vonage\Client\Credentials\Handler\BasicQueryHandler`
- `Vonage\Client\Credentials\Handler\TokenBodyHandler`
- `Vonage\Client\Credentials\Handler\TokenQueryHandler`

### `Keypair::getKey()` removed

`Vonage\Client\Credentials\Keypair::getKey()` has been removed. Use `getKeyRaw()` instead.

---

## `Vonage\Client` Changes

The following methods have been removed from `Vonage\Client`:

| Removed method | Replacement |
|---|---|
| `send(RequestInterface $request)` | Use `APIResource` directly to make HTTP requests |
| `generateJwt()` | Use `Vonage\JWT\TokenGenerator` from the `vonage/jwt` package |
| `get()` | Use `APIResource::get()` |
| `post()` | Use `APIResource::create()` |
| `put()` | Use `APIResource::update()` |
| `delete()` | Use `APIResource::delete()` |
| `postUrlEncoded()` | Use `APIResource::create()` with form-encoded body |
| `signRequest()` | Handled automatically by auth handlers |
| `authRequest()` | Handled automatically by auth handlers |
| `serialize()` / `unserialize()` | Not supported |

Passing a `Vonage\Client\Credentials\Gnp` credential to `Vonage\Client::__construct()` no longer
works; that credential class has been removed.

---

## `APIResource` Changes

`APIResource::__construct()` now requires a `Vonage\Client` instance:

```php
// Before
$api = new APIResource();
$api->setClient($client);

// After
$api = new APIResource($client);
```

---

## `APIClient` Interface Removed

`Vonage\Client\APIClient` (which declared `getAPIResource()` / `getApiResource()`) has been
removed. All module clients no longer expose this method. Do not type-hint to `APIClient` or call
`getAPIResource()` / `getApiResource()` on any module client from outside that client.

---

## `ClientAwareInterface` / `ClientAwareTrait` Removed

`Vonage\Client\ClientAwareInterface` and `Vonage\Client\ClientAwareTrait` have been removed.
`setClient()` and `getClient()` are gone from all module clients.

**Before:**
```php
$client = new SomeClient();
$client->setClient($vonageClient);
```

**After:** Inject the `APIResource` (which already holds a reference to `Vonage\Client`) via the
constructor:
```php
$api = new APIResource($vonageClient);
$client = new SomeClient($api);
```

---

## Factory / Container Changes

`Vonage\Client\Factory\FactoryInterface` now extends `Psr\Container\ContainerInterface`. The
following method renames apply:

| Old | New |
|---|---|
| `hasApi(string $name): bool` | `has(string $id): bool` |
| `getApi(string $name): mixed` | `get(string $id): mixed` |

---

## Entity Layer Removed

The following interfaces, traits, and utilities that exposed PSR-7 request/response data on entity
objects have been deleted:

- `Vonage\Entity\EntityInterface`
- `Vonage\Entity\HasEntityTrait`
- `Vonage\Entity\Psr7Trait`
- `Vonage\Entity\JsonSerializableTrait`
- `Vonage\Entity\JsonSerializableInterface`
- `Vonage\Entity\JsonUnserializableInterface`
- `Vonage\Entity\JsonResponseTrait`
- `Vonage\Entity\NoRequestResponseTrait`
- `Vonage\Entity\RequestArrayTrait`

`Vonage\Entity\IterableAPICollection::getApiResource()` has been removed.

---

## Application API

- `Application\Client::update()` is now strictly typed; ensure you pass a proper `Application`
  object.
- `ApplicationInterface` has been removed.
- `setWebhook(string $type, string $url, string $method)` (the string-argument form) has been
  removed. You must pass a `Vonage\Application\Webhook` object:

```php
// Before
$application->setWebhook('answer', 'https://example.com/answer', 'GET');

// After
use Vonage\Application\Webhook;
$application->setWebhook('answer', new Webhook('https://example.com/answer', 'GET'));
```

---

## Numbers API

- `Numbers\Client::purchase()` now requires both the number and country as arguments (previously
  the country could be omitted).
- `Numbers\Client::cancel()` no longer accepts a `$country` argument.
- `Number::jsonUnserialize()` has been removed.
- `Numbers\Hydrator` has been removed. Use
  `Vonage\Entity\Hydrator\ArrayHydrator` with a `Number` prototype instead:

```php
// Before
$hydrator = new \Vonage\Numbers\Hydrator();

// After
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Numbers\Number;

$hydrator = new ArrayHydrator();
$hydrator->setPrototype(new Number());
```

---

## Verify v1 (Vonage\Verify)

> **Recommended:** Migrate to the Verify v2 API (`Vonage\Verify2\Client`) which is the current
> production API and will continue to receive updates.

The following breaking changes apply to the legacy Verify v1 client if you still need it:

### Request class renames

| Old class | New class |
|---|---|
| `Vonage\Verify\Request` | `Vonage\Verify\StartVerification` |
| `Vonage\Verify\RequestPSD2` | `Vonage\Verify\StartPSD2` |

### `Vonage\Verify\Check` rebuilt

`Check` is now a `readonly` class called `CheckAttempt`. The old getter methods map as follows:

| Old | New |
|---|---|
| `Check::getCode()` | `CheckAttempt::$code` |
| `Check::getDate()` | `CheckAttempt::$date` |
| `Check::getStatus()` | `CheckAttempt::$status` |
| `Check::getIpAddress()` | `CheckAttempt::$ipAddress` |
| `Check::VALID` | `CheckAttempt::VALID` |
| `Check::INVALID` | `CheckAttempt::INVALID` |

### `Vonage\Verify\Client` method changes

The following client methods have been renamed or replaced:

| Deprecated (4.x) | Replacement (5.0) |
|---|---|
| `start(Request $request)` | `startVerification(StartVerification $request): string` |
| `requestPSD2(RequestPSD2 $request)` | `startPsd2Verification(StartPSD2 $request): string` |
| `trigger($verification)` | `triggerNextEvent(string $requestId): bool` |

The new methods accept dedicated value objects and return simpler types. `startVerification()` and
`startPsd2Verification()` return the request ID string directly instead of a `Verification` entity.
`triggerNextEvent()` accepts a plain request ID string instead of a `Verification` object.

> **Note:** The new `startVerification()`, `startPsd2Verification()`, and `triggerNextEvent()`
> methods are also available in the final 4.x release (4.99) to allow you to adopt the new API
> before upgrading.

```php
// Before
$request = new \Vonage\Verify\Request('14845551212', 'My App');
$verification = $client->verify()->start($request);
$requestId = $verification->getRequestId();

// After
$request = new \Vonage\Verify\StartVerification('14845551212', 'My App');
$requestId = $client->verify()->startVerification($request);
```

```php
// Before
$client->verify()->trigger($verificationObject);

// After
$client->verify()->triggerNextEvent($requestId);
```

### `Vonage\Verify\Client` constructor

The constructor now accepts an optional `Vonage\Client` as a second parameter, allowing full
initialization at construction time:

```php
// Fully initialized at construction (new in 5.0)
$verifyClient = new \Vonage\Verify\Client($apiResource, $vonageClient);

// Legacy pattern still works
$verifyClient = new \Vonage\Verify\Client($apiResource);
$verifyClient->setClient($vonageClient);
```

### `Vonage\Verify\Verification` gutted

The `Verification` entity no longer implements `ArrayAccess` and the following methods have been
removed: `setClient()`, `trigger()`, `sync()`, `cancel()`.

### Authentication change

Verify v1 requests now use `BasicHandler` authentication. Ensure your `Vonage\Client` is
configured with `Basic` credentials (API key + secret) when using the v1 client.

---

## Voice API

- `Vonage\Voice\NCCO\Action\Pay` has been removed.
- The `voiceName` parameter has been removed from all NCCO actions that previously accepted it.
- `Vonage\Voice\Call\Call` has been removed. Use `Vonage\Voice\OutboundCall` instead.
- `Vonage\Voice\Call\Inbound` has been removed.

---

## SMS API

`Vonage\SMS\Message\SMS::getErrorMessage()` has been removed. Use `getWarningMessage()` instead.

---

## Module Client Constructors

All module client constructors that previously accepted `?APIResource` (nullable) now require
a non-null `APIResource` instance. You can no longer instantiate a client without providing an
`APIResource`:

```php
// Before (worked in 4.x)
$smsClient = new \Vonage\SMS\Client();
$smsClient->setClient($vonageClient);

// After
$api = new \Vonage\Client\APIResource($vonageClient);
// ... configure $api (setBaseUri, auth handlers, etc.) ...
$smsClient = new \Vonage\SMS\Client($api);
```

In most cases you should obtain pre-configured clients through the `Vonage\Client` factory:

```php
$vonageClient->sms(); // returns a configured SMS\Client
```
