<?php

namespace Socket\Raw;

use \Exception;
use \InvalidArgumentException;

class Factory
{
    public function createClient($address)
    {
        $socket = $this->createFromString($address);
        return $socket->connect($address);
    }

    public function createServer($address)
    {
        $socket = $this->createFromString($address);
        $socket->bind($address);

        if ($socket->getType() === SOCK_STREAM) {
            $socket->listen();
        }
        return $socket;
    }

    public function createTcp4()
    {
        return $this->create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    public function createTcp6()
    {
        return $this->create(AF_INET6, SOCK_STREAM, SOL_TCP);
    }

    public function createUdp4()
    {
        return $this->create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    public function createUdp6()
    {
        return $this->create(AF_INET6, SOCK_DGRAM, SOL_UDP);
    }

    public function createUnix()
    {
        return $this->create(AF_UNIX, SOCK_STREAM, 0);
    }

    public function createUdg()
    {
        return $this->create(AF_UNIX, SOCK_DRAM, 0);
    }

    public function createIcmp()
    {
        return $this->create(AF_INET, SOCK_RAW, getprotobyname('icmp'));
    }

    public function create($domain, $type, $protocol)
    {
        $sock = socket_create($domain, $type, $protocol);
        if ($sock === false) {
            throw new Exception('Unable to create socket');
        }
        return new Socket($sock);
    }

    /**
     *
     * @param string $address (passed by reference in order to remove scheme, if present)
     * @return \Socket\Raw\Socket
     * @throws InvalidArgumentException if given address is invalid
     * @throws Exception in case creating socket failed
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
