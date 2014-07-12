<?php

use Socket\Raw\Socket;
use Socket\Raw\Factory;

(include_once __DIR__.'/../vendor/autoload.php') OR die(PHP_EOL.'ERROR: composer autoloader not found, run "composer install" or see README for instructions'.PHP_EOL);

class SocketTest extends PHPUnit_Framework_TestCase{

    /**
     * @var Socket\Raw\Factory
     * @type Factory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new Factory();
    }

    public function testConnectGoogle()
    {
        $socket = $this->factory->createClient('www.google.com:80');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('resource', gettype($socket->getResource()));

        // connecting from local address:
        $address = $socket->getSockName();
        $this->assertNotEmpty($address);

        // connected to remote/peer address:
        $address = $socket->getPeerName();
        $this->assertNotEmpty($address);

        // expect to be writable right away
        $this->assertTrue($socket->selectWrite());

        // send HTTP request to remote server
        $data = "GET / HTTP/1.1\r\nHost: www.google.com\r\n\r\n";
        $this->assertEquals(strlen($data), $socket->write($data));

        // signal we're ready with writing to this socket
        $this->assertSame($socket, $socket->shutdown(1));

        // expect to get a readable result within 10 seconds
        $this->assertTrue($socket->selectRead(10.0));

        // read just 4 bytes
        $this->assertEquals('HTTP', $socket->read(4));

        // expect there's more data in the socket
        $this->assertTrue($socket->selectRead());

        // read a whole chunk from socket
        $this->assertNotEmpty($socket->read(8192));

        $this->assertSame($socket, $socket->close());
    }

    public function testConnectFailUnbound()
    {
        try {
            $this->factory->createClient('localhost:2');
            $this->fail('Expected connection to fail');
        }
        catch (Exception $e) {
            $this->assertEquals(SOCKET_ECONNREFUSED, $e->getCode());
        }
    }

    public function testConnectAsyncGoogle()
    {
        $socket = $this->factory->createTcp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);

        $this->assertSame($socket, $socket->bind('0:0'));

        $this->assertSame($socket, $socket->setBlocking(false));

        try {
            $this->assertSame($socket, $socket->connect('www.google.com:80'));
            $this->fail('Calling connect() succeeded immediately');
        }
        catch (Exception $e) {
            // non-blocking connect() should be EINPROGRESS
            $this->assertEquals(SOCKET_EINPROGRESS, $e->getCode());

            // connection should be established within 5.0 seconds
            $this->assertTrue($socket->selectWrite(5.0));

            // confirm connection success
            $this->assertSame($socket, $socket->assertAlive());
        }

        $this->assertSame($socket, $socket->close());
    }

    public function testConnectAsyncFailUnbound()
    {
        $socket = $this->factory->createTcp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);

        $this->assertSame($socket, $socket->setBlocking(false));

        try {
            $this->assertSame($socket, $socket->connect('localhost:2'));
            $this->fail('Calling connect() succeeded immediately');
        }
        catch (Exception $e) {
            // non-blocking connect() should be EINPROGRESS
            $this->assertEquals(SOCKET_EINPROGRESS, $e->getCode());

            // connection should be rejected within 5.0 seconds
            $this->assertTrue($socket->selectWrite(5.0));

            // confirm connection success (should reject)
            try {
                $socket->assertAlive();
                $this->fail('Calling connect() succeeded');
            }
            catch (Exception $e) {
                $this->assertEquals(SOCKET_ECONNREFUSED, $e->getCode());
            }
        }

        $this->assertSame($socket, $socket->close());
    }

    public function testSelectFloat()
    {
        $socket = $this->factory->createClient('google.com:80');

        $time = microtime(true);
        $this->assertFalse($socket->selectRead(0.5));
        $time = microtime(true) - $time;

        $this->assertGreaterThan(0.5, $time);
        $this->assertLessThan(1.0, $time);
    }

    public function testConnectTimeoutGoogle()
    {
        $socket = $this->factory->createTcp4();

        $this->assertSame($socket, $socket->connectTimeout('google.com:80', 10.0));

        $socket->close();
    }

    public function testConnectTimeoutUdpImmediately()
    {
        $socket = $this->factory->createUdp4();

        $socket->connectTimeout('google.com:8000', 10);
    }

    public function testConnectTimeoutFailTimeout()
    {
        $socket = $this->factory->createTcp4();

        $this->setExpectedException('Socket\Raw\Exception', null, SOCKET_ETIMEDOUT);

        $socket->connectTimeout('google.com:80', 0.001);
    }

    public function testConnectTimeoutFailUnbound()
    {
        $socket = $this->factory->createTcp4();

        $this->setExpectedException('Socket\Raw\Exception', null, SOCKET_ECONNREFUSED);

        $socket->connectTimeout('localhost:2', 0.5);
    }

    public function testConnectTimeoutFailAlreadyConnected()
    {
        $socket = $this->factory->createClient('google.com:80');

        $this->setExpectedException('Socket\Raw\Exception', null, SOCKET_EISCONN);

        $socket->connectTimeout('google.com:8000', 10);
    }
}
