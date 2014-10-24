# clue/socket-raw [![Build Status](https://travis-ci.org/clue/php-socket-raw.svg?branch=master)](https://travis-ci.org/clue/php-socket-raw)

Simple and lightweight OOP wrapper for PHP's low level sockets extension (ext-sockets)


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

## Introduction

This library is merely a very thin wrapper around [`ext-sockets`](http://www.php.net/manual/en/book.sockets.php)
with no other external dependencies.
It exposes the whole [socket API](http://www.php.net/manual/en/ref.sockets.php) through an object-oriented
and (chainable) fluent interface which uses `Exception`s to signal error conditions.

### Creating `Socket` through `Factory`

As shown in the [quickstart example](#quickstart-example), this library uses a `Factory` pattern
as a simple API to [`socket_create()`](http://www.php.net/manual/en/function.socket-create.php).
It provides simple access to creating TCP, UDP, UNIX, UDG and ICMP protocol sockets and supports both IPv4 and IPv6 addressing.

As a further convenience it also provides a helper `Factory::createClient($address)`
for creating connected client sockets
(similar to how [`fsockopen()`](http://www.php.net/manual/en/function.fsockopen.php) or
[`stream_socket_client()`](http://www.php.net/manual/en/function.stream-socket-client.php) work).

```php
$factory = new \Socket\Raw\Factory();

$socket = $factory->createClient('tcp://www.google.com:80');
// tcp://www.google.com:80 => establish a TCP/IP stream connection socket to www.google.com on port 80
// www.google.com:80       => same as above, as scheme defaults to TCP
// udp://8.8.8.8:53        => create connectionless UDP/IP datagram socket connected to google's DNS
// tcp://[::1]:1337        => establish TCP/IPv6 stream connection socket to localhost on port 1337
// unix:///tmp/daemon.sock => connect to local unix stream socket path
// udg:///tmp/udg.socket   => create unix datagram socket
// icmp://192.168.0.1      => create a raw low-level ICMP socket (requires root!)
```

If you want to create a server side (listening) socket bound to specific address/path, you can use the helper `Factory::createServer($address)` (similar to how [`stream_socket_server()`](http://www.php.net/manual/en/function.stream-socket-server.php) works).

```php
$socket = $factory->createServer('tcp://localhost:1337');
// uses the same addressing scheme as above
```

Less commonly used, it provides access to create (unconnected) sockets for various types (`Factory::createTcp4()`, `Factory::createUnix()`, etc.) as well as supporting arbitrary protocols through `Factory::create($family, $type, $protocol)`.

### `Socket` API

As discussed above, the `Socket` class is merely an object-oriented wrapper around a socket resource. As such, it helps if you're familar with socket programming in general. You can refer to PHP's fairly good [socket API documentation](http://www.php.net/manual/en/ref.sockets.php) or the docblock comments in the `Socket` class to get you started.

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/socket-raw": "~1.1"
    }
}
```

## Tests

To run the test suite, you need PHPUnit. Go to the project root and run:
````bash
$ phpunit tests
````

Note: The test suite contains tests for ICMP sockets which require root access
on unix/linux systems. Therefor some tests will be skipped unless you run
`sudo phpunit tests` to execute the full test suite.

## License

MIT
