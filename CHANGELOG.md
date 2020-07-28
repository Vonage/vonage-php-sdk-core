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
