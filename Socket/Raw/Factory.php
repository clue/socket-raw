<?php

namespace Socket\Raw;

use \Exception;
use \InvalidArgumentException;

class Factory
{
    /**
     * create client socket connected to given target address
     *
     * @param string $address target address to connect to
     * @return \Socket\Raw\Socket
     * @throws InvalidArgumentException if given address is invalid
     * @throws Exception on error
     * @uses self::createFromString()
     * @uses Socket::connect()
     */
    public function createClient($address)
    {
        $socket = $this->createFromString($address);
        return $socket->connect($address);
    }

    /**
     * create server socket bound to given address (and start listening for streaming clients to connect to this stream socket)
     *
     * @param string $address address to bind socket to
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::createFromString()
     * @uses Socket::bind()
     * @uses Socket::listen() only for stream sockets (TCP/UNIX)
     */
    public function createServer($address)
    {
        $socket = $this->createFromString($address);
        $socket->bind($address);

        if ($socket->getType() === SOCK_STREAM) {
            $socket->listen();
        }
        return $socket;
    }

    /**
     * create TCP/IPv4 stream socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createTcp4()
    {
        return $this->create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    /**
     * create TCP/IPv6 stream socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createTcp6()
    {
        return $this->create(AF_INET6, SOCK_STREAM, SOL_TCP);
    }

    /**
     * create UDP/IPv4 datagram socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUdp4()
    {
        return $this->create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    /**
     * create UDP/IPv6 datagram socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUdp6()
    {
        return $this->create(AF_INET6, SOCK_DGRAM, SOL_UDP);
    }

    /**
     * create local UNIX stream socket
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUnix()
    {
        return $this->create(AF_UNIX, SOCK_STREAM, 0);
    }

    /**
     * create local UNIX datagram socket (UDG)
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createUdg()
    {
        return $this->create(AF_UNIX, SOCK_DRAM, 0);
    }

    /**
     * create raw ICMP datagram socket (requires root!)
     *
     * @return \Socket\Raw\Socket
     * @throws Exception on error
     * @uses self::create()
     */
    public function createIcmp()
    {
        return $this->create(AF_INET, SOCK_RAW, getprotobyname('icmp'));
    }

    /**
     * create low level socket with given arguments
     *
     * @param int $domain
     * @param int $type
     * @param int $protocol
     * @return \Socket\Raw\Socket
     * @throws Exception if creating socket fails
     * @uses socket_create()
     */
    public function create($domain, $type, $protocol)
    {
        $sock = socket_create($domain, $type, $protocol);
        if ($sock === false) {
            throw new Exception('Unable to create socket');
        }
        return new Socket($sock);
    }

    /**
     * create socket for given address (scheme defaults to TCP)
     *
     * @param string $address (passed by reference in order to remove scheme, if present)
     * @return \Socket\Raw\Socket
     * @throws InvalidArgumentException if given address is invalid
     * @throws Exception in case creating socket failed
     * @uses self::createTcp4() etc.
     */
    protected function createFromString(&$address)
    {
        $scheme = 'tcp';

        $pos = strpos($address, '://');
        if ($pos !== false) {
            $scheme = substr($address, 0, $pos);
            $address = substr($address, $pos + 3);
        }

        if (strpos($address, '[') !== false && ($scheme === 'tcp' || $scheme === 'udp')) {
            $scheme .= '6';
        }

        if ($scheme === 'tcp') {
            $socket = $this->createTcp4();
        } elseif ($scheme === 'udp') {
            $socket = $this->createUdp4();
        } elseif ($scheme === 'tcp6') {
            $socket = $this->createTcp6();
        } elseif ($scheme === 'udp6') {
            $socket = $this->createUdp6();
        } elseif ($scheme === 'unix') {
            $socket = $this->createUnix();
        } elseif ($scheme === 'udg') {
            $socket = $this->createUdg();
        } elseif ($scheme === 'icmp') {
            $socket = $this->createIcmp();
        } else {
            throw new InvalidArgumentException('Invalid address scheme given');
        }
        return $socket;
    }
}
