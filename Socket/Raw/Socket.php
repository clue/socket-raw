<?php

namespace Socket\Raw;

use \Exception;

class Socket
{
    private $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function accept()
    {
        $resource = $this->assertSuccess(socket_accept($this->resource));
        return new Socket($resource);
    }

    public function bind($address)
    {
        $this->assertSuccess(socket_bind($this->resource, $this->unformatAddress($address, $port), $port));
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

    public function connect($address)
    {
        $this->assertSuccess(socket_connect($this->resource, $this->unformatAddress($address, $port), $port));
        return $this;
    }

    public function getOption($level, $optname)
    {
        return $this->assertSuccess(socket_get_option($this->resource, $level, $optname));
    }

    public function getPeerName()
    {
        $this->assertSuccess(socket_getpeername($this->resource, $address, $port));
        return $this->formatAddress($address, $port);
    }

    public function getSockName()
    {
        $this->assertSuccess(socket_getsockname($this->resource, $address, $port));
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
        $this->assertSuccess(socket_recv($this->resource, $buffer, $length, $flags));
        return $buffer;
    }

    public function recvFrom($length, $flags, &$remote)
    {
        $this->assertSuccess(socket_recvfrom($this->resource, $buffer, $length, $flags, $address, $port));
        $remote = $this->formatAddress($address, $port);
        return $buffer;
    }

    /**
     * check socket to see if a read/recv/revFrom will not block
     *
     * @param int|NULL $sec maximum time to wait (in seconds), 0 = immediate polling, null = no limit
     * @return boolean true = socket ready (read will not block), false = timeout expired, socket is not ready
     * @uses socket_select()
     */
    public function selectRead($sec = 0)
    {
        $r = array($this->resource);
        return !!$this->assertSuccess(socket_select($r, $x = null, $x = null, $sec));
    }

    /**
     * check socket to see if a write/send/sendTo will not block
     *
     * @param int|NULL $sec maximum time to wait (in seconds), 0 = immediate polling, null = no limit
     * @return boolean true = socket ready (write will not block), false = timeout expired, socket is not ready
     * @uses socket_select()
     */
    public function selectWrite($sec = 0)
    {
        $w = array($this->resource);
        return !!$this->assertSuccess(socket_select($x = null, $w, $x = null, $sec));
    }

    public function send($buffer, $flags)
    {
        return $this->assertSuccess(socket_send($this->resource, $buffer, strlen($buffer), $flags));
    }

    public function sendTo($buffer, $flags, $remote)
    {
        return $this->assertSuccess(socket_sendto($this->resource, $buffer, strlen($buffer), $flags, $this->unformatAddress($remote, $port), $port));
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

    /**
     * get socket type as passed to socket_create()
     *
     * @return int usually either SOCK_STREAM or SOCK_DGRAM
     * @uses self::getOption()
     */
    public function getType()
    {
        return $this->getOption(SOL_SOCKET, SO_TYPE);
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
            if (strpos($address, ':') !== false) {
                $address = '[' . $address . ']';
            }
            $address .= ':' . $port;
        }
        return $address;
    }

    // [::1]:2 => ::1 2
    // test:2 => test 2
    // ::1 => ::1
    // test => test
    protected function unformatAddress($address, &$port)
    {
        $colon = strrpos($address, ':');

        // there is a colon and this is the only colon or there's a closing IPv6 bracket right before it
        if ($colon !== false && (strpos($address, ':') === $colon || strpos($address, ']') === ($colon - 1))) {
            $port = (int)substr($address, $colon + 1);
            $address = substr($address, 0, $colon);

            // remove IPv6 square brackets
            if (substr($address, 0, 1) === '[') {
                $address = substr($address, 1, -1);
            }
        }
        return $address;
    }
}
