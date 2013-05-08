<?php

use Socket\Raw\Factory;

(include_once __DIR__.'/../vendor/autoload.php') OR die(PHP_EOL.'ERROR: composer autoloader not found, run "composer install" or see README for instructions'.PHP_EOL);

class FactoryTest extends PHPUnit_Framework_TestCase{

    /**
     * @var Socket\Raw\Factory
     * @type Factory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new Factory();
    }

    public function testConstructorWorks()
    {
        $this->assertInstanceOf('Socket\Raw\Factory', $this->factory);
    }

    public function testCreateClientTcp4()
    {
        $socket = $this->factory->createClient('tcp://www.google.com:80');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateClientSchemelessTcp4()
    {
        $socket = $this->factory->createClient('www.google.com:80');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateClientTcp4Fail()
    {
        try {
            $this->factory->createClient('tcp://localhost:2');
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_ECONNREFUSED) {
                return;
            }
        }

        $this->fail('Expected to be unable to connect to localhost:2');
    }

    public function testCreateServerTcp4Random()
    {
        $socket = $this->factory->createServer('tcp://localhost:0');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateServerSchemelessTcp4Random()
    {
        $socket = $this->factory->createServer('localhost:0');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateServerUdp4Random()
    {
        $socket = $this->factory->createServer('udp://localhost:0');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateClientUdp4()
    {
        $socket = $this->factory->createClient('udp://8.8.8.8:53');

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateTcp4()
    {
        $socket = $this->factory->createTcp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateTcp6()
    {
        // skip if no IPv6

        $socket = $this->factory->createTcp6();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUdp4()
    {
        $socket = $this->factory->createUdp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUdp6()
    {
        // skip if no IPv6

        $socket = $this->factory->createUdp6();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUnix()
    {
        // skip if not unix

        $socket = $this->factory->createUnix();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUdg()
    {
        // skip if not unix

        $socket = $this->factory->createUdg();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateIcmp4()
    {
        try {
            $socket = $this->factory->createIcmp4();
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_EPERM) {
                // skip if not root
                return $this->markTestSkipped('No access to ICMPv4 socket (only root can do so)');
            }
            throw $e;
        }

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateIcmp6()
    {
        // skip if no IPv6

        try {
            $socket = $this->factory->createIcmp6();
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_EPERM) {
                // skip if not root
                return $this->markTestSkipped('No access to ICMPv6 socket (only root can do so)');
            }
            throw $e;
        }

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    /**
     * @dataProvider testCreateProvider
     */
    public function testCreate($domain, $type, $protocol)
    {
        $socket = $this->factory->create($domain, $type, $protocol);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public static function testCreateProvider()
    {
        // only return TCP/IP and UDP/IP as the above tests should already cover other sockets
        return array(
            array(AF_INET, SOCK_STREAM, SOL_TCP),
            array(AF_INET, SOCK_DGRAM, SOL_UDP)
        );
    }

    public function testCreateInvalid()
    {
        try {
            $this->factory->create(0, 1, 2);
        }
        catch (Exception $e) {
            return;
        }
        $this->fail();
    }

    public function testCreatePair()
    {
        // skip if not unix

        $sockets = $this->factory->createPair(AF_UNIX, SOCK_STREAM, 0);

        $this->assertCount(2, $sockets);
        $this->assertInstanceOf('Socket\Raw\Socket', $sockets[0]);
        $this->assertInstanceOf('Socket\Raw\Socket', $sockets[1]);
    }

    public function testCreatePairInvalid()
    {
        try {
            $this->factory->createPair(0, 1, 2);
        }
        catch (Exception $e) {
            return;
        }
        $this->fail();
    }

    public function testCreateListenRandom()
    {
        // listen on a random free port
        $socket = $this->factory->createListen(0);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateListenInvalid()
    {
        try {
            // should only support passing a port number
            // turns out excessive numbers seem to be sanitized modulo maximum port range
            $this->factory->createListen('localhost',-1);
        }
        catch (Exception $e) {
            return;
        }
        $this->fail('Creating listening port should fail');
    }

    public function testCreateFromStringTcp4()
    {
        $address = 'tcp://127.0.0.1:80';
        $socket = $this->factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('127.0.0.1:80', $address);
        $this->assertEquals('tcp', $scheme);
    }

    /**
     * assume default scheme 'tcp'
     */
    public function testCreateFromStringSchemelessTcp4()
    {
        $address = '127.0.0.1:80';
        $socket = $this->factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('127.0.0.1:80', $address);
        $this->assertEquals('tcp', $scheme);
    }

    /**
     * scheme is actually 'tcp6' for IPv6 addresses
     */
    public function testCreateFromStringTcp6()
    {
        $address = 'tcp://[::1]:80';
        $socket = $this->factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('[::1]:80', $address);
        $this->assertEquals('tcp6', $scheme);
    }

    /**
     * assume scheme 'tcp6' for IPv6 addresses
     */
    public function testCreateFromStringSchemelessTcp6()
    {
        $address = '[::1]:80';
        $socket = $this->factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('[::1]:80', $address);
        $this->assertEquals('tcp6', $scheme);
    }

    /**
     * creating socket for invalid scheme should fail
     */
    public function testCreateFromStringInvalid()
    {
        $address = 'invalid://whatever';
        try {
            $this->factory->createFromString($address, $scheme);
        }
        catch (Exception $e) {
            return;
        }
        $this->fail('Creating socket for invalid scheme should fail');
    }

}
