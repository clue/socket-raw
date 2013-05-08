<?php

use Socket\Raw\Factory;

(include_once __DIR__.'/../vendor/autoload.php') OR die(PHP_EOL.'ERROR: composer autoloader not found, run "composer install" or see README for instructions'.PHP_EOL);

class FactoryTest extends PHPUnit_Framework_TestCase{
//     public function setUp(){
//         $loop = React\EventLoop\Factory::create();

//         $dnsResolverFactory = new React\Dns\Resolver\Factory();
//         $dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);

//         $factory = new Socks\Factory($loop, $dns);

//         $this->client = $factory->createClient('127.0.0.1', 9050);
//     }

//     /**
//      * @expectedException UnexpectedValueException
//      * @dataProvider providerInvalidAuthVersion
//      */
//     public function testInvalidAuthVersion($version)
//     {
//         $this->client->setAuth('username', 'password');
//         $this->client->setProtocolVersion($version);
//     }

//     public function providerInvalidAuthVersion()
//     {
//         return array(array('4'), array('4a'));
//     }

    public function testConstructorWorks()
    {
        $factory = new Factory();
        $this->assertInstanceOf('Socket\Raw\Factory', $factory);
    }

    public function testCreateTcp4()
    {
        $factory = new Factory();
        $socket = $factory->createTcp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateTcp6()
    {
        // skip if no IPv6

        $factory = new Factory();
        $socket = $factory->createTcp6();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUdp4()
    {
        $factory = new Factory();
        $socket = $factory->createUdp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUdp6()
    {
        // skip if no IPv6

        $factory = new Factory();
        $socket = $factory->createUdp6();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUnix()
    {
        // skip if not unix

        $factory = new Factory();
        $socket = $factory->createUnix();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateUdg()
    {
        // skip if not unix

        $factory = new Factory();
        $socket = $factory->createUdg();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateIcmp4()
    {
        $factory = new Factory();
        try {
            $socket = $factory->createIcmp4();
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

        $factory = new Factory();
        try {
            $socket = $factory->createIcmp6();
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
        $factory = new Factory();
        $socket = $factory->create($domain, $type, $protocol);

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

    public function testCreatePair()
    {
        // skip if not unix

        $factory = new Factory();
        $sockets = $factory->createPair(AF_UNIX, SOCK_STREAM, 0);

        $this->assertCount(2, $sockets);
        $this->assertInstanceOf('Socket\Raw\Socket', $sockets[0]);
        $this->assertInstanceOf('Socket\Raw\Socket', $sockets[1]);
    }

    public function testCreateListenRandom()
    {
        $factory = new Factory();

        // listen on a random free port
        $socket = $factory->createListen(0);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateFromStringTcp4()
    {
        $factory = new Factory();

        $address = 'tcp://127.0.0.1:80';
        $socket = $factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('127.0.0.1:80', $address);
        $this->assertEquals('tcp', $scheme);
    }

    /**
     * assume default scheme 'tcp'
     */
    public function testCreateFromStringSchemelessTcp4()
    {
        $factory = new Factory();

        $address = '127.0.0.1:80';
        $socket = $factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('127.0.0.1:80', $address);
        $this->assertEquals('tcp', $scheme);
    }

    /**
     * scheme is actually 'tcp6' for IPv6 addresses
     */
    public function testCreateFromStringTcp6()
    {
        $factory = new Factory();

        $address = 'tcp://[::1]:80';
        $socket = $factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('[::1]:80', $address);
        $this->assertEquals('tcp6', $scheme);
    }

    /**
     * assume scheme 'tcp6' for IPv6 addresses
     */
    public function testCreateFromStringSchemelessTcp6()
    {
        $factory = new Factory();

        $address = '[::1]:80';
        $socket = $factory->createFromString($address, $scheme);

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
        $this->assertEquals('[::1]:80', $address);
        $this->assertEquals('tcp6', $scheme);
    }

}
