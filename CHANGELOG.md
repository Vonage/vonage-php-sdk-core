# 2.10.0

### Fixed

* PHPUnit tests now no longer throw `prophesize()` depreciation notices

### Changed

* Maintainer and Contribution documents changed to reflect current ownership
* All test cases now extend off a the new `VonageTestCase` class that implements the `ProphesizeTrait`

# 2.9.3

### Fixed

* Removed the automatic unicode detection to allow for intentional selection.
* Changed Readme to include how to fire test suite from composer

# 2.9.2

### Fixed

* #276 - JWTs with `sub` should now generate properly under newer PHP versions

# 2.9.1

### Fixed

* #282 - SMS Throttling response is now handled as milliseconds instead of seconds
* #282 - Fixed regex to not consume API rate limiting error and basically time out PHP scripts

# 2.9.0

### Changed

* Nexmo/nexmo-laravel#62 - Landline Toll Free numbers can be searched for

# 2.8.1

### Fixed

* #278 - Fixed issue retrieving Conversations and Users clients where the service locator didn't know what to do

### Changed

* #283 - Moved auth logic to individual handlers, to better prepare for a fix where Containers do not allow Signature and Token auth
# 2.8.0

### Added

* #272 - Added support for PSR-3 compatible logging solutions and a new debug feature to log requests/responses
* #274 - Added support for the detail field on some new Voice API incoming events (https://developer.nexmo.com/voice/voice-api/webhook-reference#event-webhook)
* #273 - Added new content-id and entity-id fields to support regional SMS requirements, and a shortcut for enabling DLT on Indian-based SMS

# 2.7.1

### Changed

* #270 - Use the actual Guzzle package version to determine of 6 or 7 is in the project

# 2.7.0

### Added

* #269 - Added PHP 8 Support

# 2.6.0

### Added

* #265 - Added support for Language and Style for NCCO Talk action

### Changed

* #257 Dropped support for PHPUnit 7
* #257 Added missing PHPDoc blocks
* #257 Added missing return type hints
* #257 Replaced qualifiers with imports
* #257 Updated and optimized examples
* #257 Applied multiple code optimizations (especially for PHP 7.2+) and simplified some logic
* #257 Updated code styling to match PSR standards
* #257 Re-ordered imports where necessary
* #257 Updated tests to get rid of deprecation messages
* #257 Fixed namespace declarations in tests
* #257 Updated code style to PSR-12
* #257 Updated phpunit.xml.dist
* #257 Added Roave Security Advisories as dev-requirement to prevent usage of packages with known security vulnerabilities
* #257 Replaced estahn/phpunit-json-assertions with martin-helmich/phpunit-json-assert due do compatibility issues with PHPUnit
* #257 Removed test build for PHP 7.1 in .travis.yml
* #257 Added missing punctuation in CONTRIBUTING.md
* #257 Updated contact email address in CODE_OF_CONDUCT.md

### Deprecated

* #265 - Deprecated use of VoiceName for NCCO Talk action

### Fixed

* #257 Fixed namespaces (Zend => Laminas, Nexmo => Vonage)
* #257 Fixed condition in Verify\Request::setCodeLength
* #257 Fixed typos and some wording in README.md

### Removed

* Removed `examples/` directory as the code snippets repo is much more up-to-date

# 2.5.0

### Changed

- #260 - Swapped out `ocramius/package-versions` for `composer/package-versions-deprecated` to work with Composer 2

# 2.4.1

### Changed

* #256 - Added support for PHPUnit 8

### Fixed

* #253, #254 - Fixed some typos in the README
* #255 - `\Vonage\Numbers\Client::searchAvailable()` now correctly handles filters using `FilterInterface`

# 2.4.0

### Changed

* #250 - Bumped minimum PHP version to 7.2
* #250 - Now supports Guzzle 7 automatically, and swaps to Guzzle 7 as a dev dependency

# 2.3.3

### Fixed

* #252 - Connect action's `eventUrl` was being set as a string, changed to single element array of strings

# 2.3.2

### Added

* #248 - Added `\Vonage\Client\MapFactory::make()` to always instatiate new objects

### Fixed

* #248 - Fixed type in URL for Account Client Factory

# 2.3.1

### Added

* #247 - Fixed missing fields on Standard/Advanced number insight getters

### Fixed

* #246 - Fixed badge URLs in README

# 2.3.0

### Added

* Support for the PSD2 Verify endpoints for EU customers
* `vonage/nexmo-bridge` as a dependency so `\Nexmo` namespaced code works with the new `\Vonage` namespace
* Calls using `\Vonage\Client\APIResource` can now specify headers for individual requests

### Changed

* Namespace changed from `\Nexmo` to `\Vonage` for all classes, interfaces, and traits

### Fixed

* Base URL overrides were not being pushed up properly
* JSON payload for transferring via NCCO or URL was malformed

# 2.2.3

### Added

* Added country as a search option for Nexmo\Numbers\Client::searchOwned()

# 2.2.2

### Fixed

* #235 - Adds a fix for calling the calls() API client

# 2.2.1

### Added

* Allow Conversations NCCO to set event URL information
* Added missing Notify webhook and new ASR code

### Changed

* NCCOs now set let default options

### Removed

* Redundant comments in client for sms() and verify() clients

# 2.2.0
This release focuses on deprecation of dead and old code, and preps many internal changes in regards to v3.0.0. Where possible upcoming v3.0.0 changes were backported where backward-compatibility could be maintained.

### Added

* New Voice and SMS interfaces, accessible through `$client->voice()` and `$client->sms()`, respectively
* Added user deprecation warnings, which can be turned on and off via the `Nexmo\Client` "show_deprecations" config option. This can help devs update in preparation in v3.0.0, and will be utilized in the future as things are deprecated.
* Many objects now have a `toArray()` serialization method, to discourage direct use of `jsonSerialize()`
* Many objects now support a `fromArray()` hydration method
* Better incoming webhook support for SMS and Voice events
* NCCO builder for Voice

### Changed

* API handling code has been conglomerated in `Nexmo\Client\APIResource` and `Nexmo\Entity\IterableAPICollection`
* All APIs ported over to the new API handling layer
* Internal Service Locator `Nexmo\Client\Factory\MapFactory` is now PSR-11 compliant, and can use factories
* Most Verify methods in the client now prefer string Request IDs
* Verify now prefers `Nexmo\Verify\Request` for starting new Verification requests

### Deprecated
For a detailed list of things that may impact an application, enable the `show_deprecations` `Nexmo\Client` option to see deprecation notices for specific code flows.

* Most forms of array access are now deprecated
* Using service layers like `$client->messages($filter)` to search has been deprecated in favor of bespoke search methods
* Requests/Responses on exceptions and entities are deprecated, and now exist in the service layer or collection
* Most methods that took raw arrays now prefer objects
* `Nexmo\Verify\Verification` objects full functionality has been deprecated, and will be used only as a value object for search requests in the future
* `Nexmo\Conversations` and `Nexmo\User` have been deprecated and will be removed in the future as the feature is in Beta status and has diverged from this implementation
* `Nexmo\Voice\Call` and `Nexmo\Voice\Message` have been deprecated and will be removed in the future as the TTS API is deprecated
* SMS searching has been deprecated and will be removed in a future update

### Removed
* No features or classes have been removed in this update, it is functionally compatible with v2.1.0 other than deprecation notices and new features.

### Fixed
* No direct bugs have been fixed as this release was designed to be as compatible with v2.1.0 as possible, however:
  * #177 should be better handled by a centralized `Nexmo\Client\Exception\ThrottleException` and has been implemented in SMS and the Numbers API
  * #219 is implicitly fixed in `Nexmo\SMS\Client::send()` as it now returns a fully hydrated collection object as a response, however this needs to be updated in Laravel itself via an update to `nexmo/laravel` and `laravel/nexmo-notification-channel `
  * #221 is implicitly fixed in `Nexmo\SMS\Client::send()` as it now returns a fully hydrated collection object that is much more up-front it is not a single object
  * #227 is implicitly fixed in `Nexmo\SMS\Webhook\InboundSMS`

### Security

* There were no known security vulnerabilities reported
