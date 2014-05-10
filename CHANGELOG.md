# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

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
