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
        // skip if not root

        $factory = new Factory();
        $socket = $factory->createIcmp4();

        $this->assertInstanceOf('Socket\Raw\Socket', $socket);
    }

    public function testCreateIcmp6()
    {
        // skip if not root or no IPv6

        $factory = new Factory();
        $socket = $factory->createIcmp6();

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
        return array(
            array(AF_INET, SOCK_STREAM, SOL_TCP),
            array(AF_INET, SOCK_DGRAM, SOL_UDP)
        );
    }

}
