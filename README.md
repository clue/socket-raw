# clue/socket-raw [![Build Status](https://travis-ci.org/clue/php-socket-raw.svg?branch=master)](https://travis-ci.org/clue/php-socket-raw)

Simple and lightweight OOP wrapper for PHP's low level sockets extension (ext-sockets)

PHP offers two networking APIs, the newer [streams API](http://php.net/manual/en/book.stream.php) and the older [socket API](http://www.php.net/manual/en/ref.sockets.php).
While the former has been a huge step forward in generalizing various streaming resources,
it lacks some of the advanced features of the original and much more low level socket API.
This lightweight library exposes this socket API in a modern way by providing a thin wrapper around the underlying API.

* **Full socket API** -
  It exposes the whole [socket API](http://www.php.net/manual/en/ref.sockets.php) through a *sane* object-oriented interface.
  Provides convenience methods for common operations as well as exposing all underlying methods and options.
* **Fluent interface** -
  Uses a fluent interface so you can easily chain method calls.
  Error conditions will be signalled using `Exception`s instead of relying on cumbersome return codes.
* **Lightweight, SOLID design** -
  Provides a thin abstraction that is [*just good enough*](http://en.wikipedia.org/wiki/Principle_of_good_enough)
  and does not get in your way.
  This library is merely a very thin wrapper and has no other external dependencies.
* **Good test coverage** -
  Comes with an automated test suite and is regularly tested in the *real world*

**Table of contents**

* [Quickstart example](#quickstart-example)
* [Usage](#usage)
  * [Factory](#factory)
    * [createClient()](#createclient)
    * [createServer()](#createserver)
    * [create*()](#create)
  * [Socket](#socket)
    * [Methods](#methods)
      * [Data I/O](#data-io)
      * [Unconnected I/O](#unconnected-io)
      * [Non-blocking (async) I/O](#non-blocking-async-io)
      * [Connection handling](#connection-handling)
* [Install](#install)
* [Tests](#tests)
* [License](#license)

## Quickstart example

Once [installed](#install), you can use the following example to send and receive HTTP messages:

```php
$factory = new \Socket\Raw\Factory();

$socket = $factory->createClient('www.google.com:80');
echo 'Connected to ' . $socket->getPeerName() . PHP_EOL;

// send simple HTTP request to remote side
$socket->write("GET / HTTP/1.1\r\n\Host: www.google.com\r\n\r\n");

// receive and dump HTTP response
var_dump($socket->read(8192));

$socket->close();
```

See also the [examples](examples).

## Usage

### Factory

As shown in the [quickstart example](#quickstart-example), this library uses a `Factory` pattern
as a simple API to [`socket_create()`](http://www.php.net/manual/en/function.socket-create.php).
It provides simple access to creating TCP, UDP, UNIX, UDG and ICMP protocol sockets and supports both IPv4 and IPv6 addressing.

```php
$factory = new \Socket\Raw\Factory();
```

#### createClient()

The `createClient($address)` method is the most convenient method for creating connected client sockets
(similar to how [`fsockopen()`](http://www.php.net/manual/en/function.fsockopen.php) or
[`stream_socket_client()`](http://www.php.net/manual/en/function.stream-socket-client.php) work).

```php
// establish a TCP/IP stream connection socket to www.google.com on port 80
$socket = $factory->createClient('tcp://www.google.com:80');

// same as above, as scheme defaults to TCP
$socket = $factory->createClient('www.google.com:80');

// create connectionless UDP/IP datagram socket connected to google's DNS
$socket = $factory->createClient('udp://8.8.8.8:53');

// establish TCP/IPv6 stream connection socket to localhost on port 1337
$socket = $factory->createClient('tcp://[::1]:1337');

// connect to local Unix stream socket path
$socket = $factory->createClient('unix:///tmp/daemon.sock');

// create Unix datagram socket
$socket = $factory->createClient('udg:///tmp/udg.socket');

// create a raw low-level ICMP socket (requires root!)
$socket = $factory->createClient('icmp://192.168.0.1');
```

#### createServer()

The `createServer($address)` method can be used to create a server side (listening) socket bound to specific address/path
(similar to how [`stream_socket_server()`](http://www.php.net/manual/en/function.stream-socket-server.php) works).
It accepts the same addressing scheme as the [`createClient()`](#createClient) method.

```php
// create a TCP/IP stream connection socket server on port 1337
$socket = $factory->createServer('tcp://localhost:1337');

// create a UDP/IPv6 datagram socket server on port 1337
$socket = $factory->createServer('udp://[::1]:1337');
```

#### create*()

Less commonly used, the `Factory` provides access to creating (unconnected) sockets for various socket types:

```php
$socket = $factory->createTcp4();
$socket = $factory->createTcp6();

$socket = $factory->createUdp4();
$socket = $factory->createUdp6();

$socket = $factory->createUnix();
$socket = $factory->createUdg();

$socket = $factory->createIcmp4();
$socket = $factory->createIcmp6();
```

You can also create arbitrary socket protocol types through the underlying mechanism:

```php
$factory->create($family, $type, $protocol);
```

### Socket

As discussed above, the `Socket` class is merely an object-oriented wrapper around a socket resource. As such, it helps if you're familar with socket programming in general.

The recommended way to create a `Socket` instance is via the above [`Factory`](#factory).

#### Methods

All low-level socket operations are available as methods on the `Socket` class.

You can refer to PHP's fairly good [socket API documentation](http://www.php.net/manual/en/ref.sockets.php) or the docblock comments in the [`Socket` class](src/Socket.php) to get you started.

##### Data I/O:

```
$socket->write('data');
$data = $socket->read(8192);
```

##### Unconnected I/O:

```
$socket->sendTo('data', $flags, $remote);
$data = $socket->rcvFrom(8192, $flags, $remote);
```

##### Non-blocking (async) I/O:

```
$socket->setBlocking(false);
$socket->selectRead();
$socket->selectWrite();
```

##### Connection handling:

```php
$client = $socket->accept();
$socket->bind($address);
$socket->connect($address);
$socket->shutdown();
$socket->close();
```

## Install

The recommended way to install this library is [through Composer](http://getcomposer.org).
[New to Composer?](http://getcomposer.org/doc/00-intro.md)

```bash
$ composer require clue/socket-raw:~1.2
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

## Tests

To run the test suite, you need PHPUnit. Go to the project root and run:

````bash
$ phpunit tests
````

> Note: The test suite contains tests for ICMP sockets which require root access
on unix/linux systems. Therefor some tests will be skipped unless you run
`sudo phpunit tests` to execute the full test suite.

## License

MIT
