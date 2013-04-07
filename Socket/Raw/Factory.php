<?php

namespace Socket\Raw;

use \Exception;

class Factory
{
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
}
