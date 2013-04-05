<?php

namespace Socket\Raw;

use \Exception;

class Factory
{
    public function createUdp4()
    {
        return $this->create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    public function createUdp6()
    {
        return $this->create(AF_INET6, SOCK_DGRAM, SOL_UDP);
    }

    private function create($domain, $type, $protocol)
    {
        $sock = socket_create($domain, $type, $protocol);
        if ($sock === false) {
            throw new Exception('Unable to create socket');
        }
        return new Socket($sock);
    }
}
