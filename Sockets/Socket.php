<?php

namespace Sockets;

use \Exception;

class Socket
{
    private $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function accept()
    {
        $resource = $this->assertSuccess(socket_accept($this->resource));
        return new Socket($resource);
    }

    public function bind($address, $port = 0)
    {
        $this->assertSuccess(socket_bind($this->resource, $address, $port));
        return $this;
    }

    public function close()
    {
        if ($this->resource !== false) {
            socket_close($this->resource);
            $this->resource = false;
        }
        return $this;
    }

    public function connect($address, $port = 0)
    {
        $this->assertSuccess(socket_connect($this->resource, $address, $port));
        return $this;
    }

    public function getOption($level, $optname)
    {
        return $this->assertSuccess(socket_get_option($this->resource, $level, $optname));
    }

    public function getPeerName()
    {
        $this->assertSuccess(socket_getpeername($this->resource, $address = '', $port = null));
        return $this->formatAddress($address, $port);
    }

    public function getSockName()
    {
        $this->assertSuccess(socket_getsockname($this->resource, $address = '', $port = null));
        return $this->formatAddress($address, $port);
    }

    public function listen($backlog = 0)
    {
        $this->assertSuccess(socket_listen($this->resource, $backlog));
        return $this;
    }

    public function read($length)
    {
        return $this->assertSuccess(socket_read($this->resource, $length));
    }

    public function recv($length, $flags)
    {
        $this->assertSuccess(socket_recv($this->resource, $buffer = '', $length, $flags));
        return $buffer;
    }

    public function recvFrom($length, $flags, &$remote)
    {
        $this->assertSuccess(socket_recvfrom($this->resource, $buffer = '', $length, $flags, $address = '', $port = null));
        $remote = $this->formatAddress($address, $port);
        return $buffer;
    }

    public function selectRead()
    {
        return !!$this->assertSuccess(socket_select($r = array($this->resource), $x = null, $x = null, 0));
    }

    public function selectWrite()
    {
        return !!$this->assertSuccess(socket_select($x = null, $w = array($this->resource), $x = null, 0));
    }

    public function send($buffer, $flags)
    {
        return $this->assertSuccess(socket_send($this->resource, $buffer, strlen($buffer), $flags));
    }

    public function sendTo($buffer, $flags, $remote)
    {
        list($address, $port) = $this->unformatAddress($remote);
        return $this->assertSuccess(socket_sendto($this->resource, $buffer, strlen($buffer), $flags, $address, $port));
    }

    public function setBlock()
    {
        $this->assertSuccess(socket_set_block($this->resource));
        return $this;
    }

    public function setUnblock()
    {
        $this->assertSuccess(socket_set_nonblock($this->resource));
        return $this;
    }

    public function setOption($level, $optname, $optval)
    {
        $this->assertSuccess(socket_set_option($this->resource, $level, $optname, $optval));
        return $this;
    }

    public function shutdown($how = 2)
    {
        $this->assertSuccess(socket_shutdown($this->resource, $how));
        return $this;
    }

    public function write($buffer)
    {
        return $this->assertSuccess(socket_write($this->resource, $buffer));
    }

    protected function assertSuccess($val)
    {
        if ($val === false) {
            throw new Exception('Socket operation failed: ' . socket_strerror(socket_last_error($this->resource)));
        }
        return $val;
    }

    protected function formatAddress($address, $port)
    {
        if ($port !== null) {
            // TODO: IPv6
            $address .= ':' . $port;
        }
        return $address;
    }

    protected function unformatAddress($address)
    {
        // TODO: IPv6
        return explode(':', $address);
    }
}
