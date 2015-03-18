# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

## 1.2.0 (2015-03-18)

* Feature: Expose optional `$type` parameter for `Socket::read()`
  ([#16](https://github.com/clue/php-socket-raw/pull/16) by @Elbandi)

## 1.1.0 (2014-10-24)

* Feature: Accept float timeouts like `0.5` for `Socket::selectRead()` and `Socket::selectWrite()`.
  ([#8](https://github.com/clue/php-socket-raw/issues/8))

* Feature: Add new `Socket::connectTimeout()` method.
  ([#11](https://github.com/clue/php-socket-raw/pull/11))

* Fix: Close invalid socket resource when `Factory` fails to create a `Socket`.
  ([#12](https://github.com/clue/php-socket-raw/pull/12))

* Fix: Calling `accept()` on an idle server socket emits right error code and message.
  ([#14](https://github.com/clue/php-socket-raw/pull/14))

## 1.0.0 (2014-05-10)

* Feature: Improved errors reporting through dedicated `Exception`
  ([#6](https://github.com/clue/socket-raw/pull/6))
* Feature: Support HHVM
  ([#5](https://github.com/clue/socket-raw/pull/5))
* Use PSR-4 layout
  ([#3](https://github.com/clue/socket-raw/pull/3))
* Continuous integration via Travis CI

## 0.1.2 (2013-05-09)

* Fix: The `Factory::createUdg()` now returns the right socket type.
* Fix: Fix ICMPv6 addressing to not require square brackets because it does not
  use ports.
* Extended test suite.

## 0.1.1 (2013-04-18)

* Fix: Raw sockets now correctly report no port instead of a `0` port.

## 0.1.0 (2013-04-10)

* First tagged release
