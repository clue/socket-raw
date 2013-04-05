<?php

namespace Sockets;

use React\Loop\LoopInterface;
use \Exception;

class Factory
{
    private $pollInterval = 0.01;
    
    public function __construct(LoopInterface $loop)
    {
        if (!function_exists('socket_create')) {
            throw new Exception('Sockets extension not loaded');
        }
        $this->loop = $loop;
    }
    
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
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            throw new Exception('Unable to create socket');
        }
        return new Socket($sock, $this->pollInterval, $this->loop);
    }
}
