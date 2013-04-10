<?php

namespace Socket\Raw;

use \Exception;

/**
 * simple and lightweight OOP wrapper for the low level sockets extension (ext-sockets)
 *
 * @author clue
 * @link https://github.com/clue/socket-raw
 */
class Socket
{
    /**
     * reference to actual socket resource
     *
     * @var resource
     */
    private $resource;

    /**
     * instanciate socket wrapper for given socket resource
     *
     * should usually not be called manually, see Factory
     *
     * @param resource $resource
     * @see Factory as the preferred (and simplest) way to construct socket instances
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * get actual socket resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * accept an incomming connection on this listening socket
     *
     * @return \Socket\Raw\Socket new connected socket used for communication
     * @throws Exception on error, if this is not a listening socket or there's no connection pending
     * @see self::selectRead() to check if this listening socket can accept()
     * @see Factory::createServer() to create a listening socket
     * @see self::listen() has to be called first
     * @uses socket_accept()
     */
    public function accept()
    {
        $resource = $this->assertSuccess(socket_accept($this->resource));
        return new Socket($resource);
    }

    /**
     * binds a name/address/path to this socket
     *
     * has to be called before issuing connect() or listen()
     *
     * @param string $address either of IPv4:port, hostname:port, [IPv6]:port, unix-path
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses socket_bind()
     */
    public function bind($address)
    {
        $this->assertSuccess(socket_bind($this->resource, $this->unformatAddress($address, $port), $port));
        return $this;
    }

    /**
     * close this socket
     *
     * ATTENTION: make sure to NOT re-use this socket instance after closing it!
     * its socket resource is removed and all furhter operations will fail!
     *
     * @return self $this (chainable)
     * @see self::shutdown() should be called before closing socket
     * @uses socket_close()
     */
    public function close()
    {
        if ($this->resource !== false) {
            socket_close($this->resource);
            $this->resource = false;
        }
        return $this;
    }

    /**
     * initiate a connection to given address
     *
     * @param string $address either of IPv4:port, hostname:port, [IPv6]:port, unix-path
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses socket_connect()
     */
    public function connect($address)
    {
        $this->assertSuccess(socket_connect($this->resource, $this->unformatAddress($address, $port), $port));
        return $this;
    }

    /**
     * get socket option
     *
     * @param int $level
     * @param int $optname
     * @return mixed
     * @throws Exception on error
     * @uses socket_get_option()
     */
    public function getOption($level, $optname)
    {
        return $this->assertSuccess(socket_get_option($this->resource, $level, $optname));
    }

    /**
     * get remote side's address/path
     *
     * @return string
     * @throws Exception on error
     * @uses socket_getpeername()
     */
    public function getPeerName()
    {
        $this->assertSuccess(socket_getpeername($this->resource, $address, $port));
        return $this->formatAddress($address, $port);
    }

    /**
     * get local side's address/path
     *
     * @return string
     * @throws Exception on error
     * @uses socket_getsockname()
     */
    public function getSockName()
    {
        $this->assertSuccess(socket_getsockname($this->resource, $address, $port));
        return $this->formatAddress($address, $port);
    }

    /**
     * start listen for incoming connections
     *
     * @param int $backlog maximum number of incoming connections to be queued
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::bind() has to be called first to bind name to socket
     * @uses socket_listen()
     */
    public function listen($backlog = 0)
    {
        $this->assertSuccess(socket_listen($this->resource, $backlog));
        return $this;
    }

    /**
     * read up to $length bytes from connect()ed / accept()ed socket
     *
     * @param int $length maximum length to read
     * @return string
     * @throws Exception on error
     * @see self::recv() if you need to pass flags
     * @uses socket_read()
     */
    public function read($length)
    {
        return $this->assertSuccess(socket_read($this->resource, $length));
    }

    /**
     * receive up to $length bytes from connect()ed / accept()ed socket
     *
     * @param int $length maximum length to read
     * @param int $flags
     * @return string
     * @throws Exception on error
     * @see self::read() if you do not need to pass $flags
     * @see self::recvFrom() if your socket is not connect()ed
     * @uses socket_recv()
     */
    public function recv($length, $flags)
    {
        $this->assertSuccess(socket_recv($this->resource, $buffer, $length, $flags));
        return $buffer;
    }

    /**
     * receive up to $length bytes from socket
     *
     * @param int    $length maximum length to read
     * @param int    $flags
     * @param string $remote reference will be filled with remote/peer address/path
     * @return string
     * @throws Exception on error
     * @see self::recv() if your socket is connect()ed
     * @uses socket_recvfrom()
     */
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
        return !!$this->assertSuccess(socket_select($r, $x, $x, $sec));
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
        return !!$this->assertSuccess(socket_select($x, $w, $x, $sec));
    }

    /**
     * send given $buffer to connect()ed / accept()ed socket
     *
     * @param string $buffer
     * @param int    $flags
     * @return int number of bytes actually written (make sure to check against given buffer length!)
     * @throws Exception on error
     * @see self::write() if you do not need to pass $flags
     * @see self::sendTo() if your socket is not connect()ed
     * @uses socket_send()
     */
    public function send($buffer, $flags)
    {
        return $this->assertSuccess(socket_send($this->resource, $buffer, strlen($buffer), $flags));
    }

    /**
     * send given $buffer to socket
     *
     * @param string $buffer
     * @param int    $flags
     * @param string $remote remote/peer address/path
     * @return int number of bytes actually written
     * @throws Exception on error
     * @see self::send() if your socket is connect()ed
     * @uses socket_sendto()
     */
    public function sendTo($buffer, $flags, $remote)
    {
        return $this->assertSuccess(socket_sendto($this->resource, $buffer, strlen($buffer), $flags, $this->unformatAddress($remote, $port), $port));
    }

    /**
     * enable/disable blocking/nonblocking mode (O_NONBLOCK flag)
     *
     * @param boolean $toggle
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses socket_set_block()
     * @uses socket_set_nonblock()
     */
    public function setBlocking($toggle = true)
    {
        $this->assertSuccess($toggle ? socket_set_block($this->resource) : socket_set_nonblock($this->resource));
        return $this;
    }

    /**
     * set socket option
     *
     * @param int   $level
     * @param int   $optname
     * @param mixed $optval
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::getOption()
     * @uses socket_set_option()
     */
    public function setOption($level, $optname, $optval)
    {
        $this->assertSuccess(socket_set_option($this->resource, $level, $optname, $optval));
        return $this;
    }

    /**
     * shuts down socket for receiving, sending or both
     *
     * @param int $how 0 = shutdown reading, 1 = shutdown writing, 2 = shutdown reading and writing
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::close()
     * @uses socket_shutdown()
     */
    public function shutdown($how = 2)
    {
        $this->assertSuccess(socket_shutdown($this->resource, $how));
        return $this;
    }

    /**
     * write $buffer to connect()ed / accept()ed socket
     *
     * @param string $buffer
     * @return int number of bytes actually written
     * @throws Exception on error
     * @see self::send() if you need to pass flags
     * @uses socket_write()
     */
    public function write($buffer)
    {
        return $this->assertSuccess(socket_write($this->resource, $buffer));
    }

    /**
     * get socket type as passed to socket_create()
     *
     * @return int usually either SOCK_STREAM or SOCK_DGRAM
     * @throws Exception on error
     * @uses self::getOption()
     */
    public function getType()
    {
        return $this->getOption(SOL_SOCKET, SO_TYPE);
    }

    /**
     * assert that this socket is alive and its error code is 0
     *
     * This will fetch and reset the current socket error code from the
     * socket and options and will throw an Exception along with error
     * message and code if the code is not 0, i.e. if it does indicate
     * an error situation.
     *
     * Calling this method should not be needed in most cases and is
     * likely to not throw an Exception. Each socket operation like
     * connect(), send(), etc. will throw a dedicated Exception in case
     * of an error anyway.
     *
     * @return self $this (chainable)
     * @throws Exception if error code is not 0
     * @uses self::getOption() to retrieve and clear current error code
     * @uses self::getErrorMessage() to translate error code to
     */
    public function assertAlive()
    {
        $code = $this->getOption(SOL_SOCKET, SO_ERROR);
        if ($code !== 0) {
            throw new Exception('Socket error: ' . $this->getErrorMessage($code), $code);
        }
        return $this;
    }

    /**
     * assert the given $val is not boolean false, which is an error condition
     *
     * @param mixed $val
     * @return mixed given $val as-is
     * @throws Exception if given $val is boolean false
     * @uses socket_last_error() to get last error code
     * @uses socket_clear_error() to clear error code
     * @uses self::getErrorMessage() to translate error code to error message
     */
    protected function assertSuccess($val)
    {
        if ($val === false) {
            $code = socket_last_error($this->resource);
            socket_clear_error($this->resource);

            throw new Exception('Socket operation failed: ' . $this->getErrorMessage($code), $code);
        }
        return $val;
    }

    /**
     * get error message for given error code
     *
     * @param int $code error code
     * @return string
     * @uses socket_strerror() to translate error code to error message
     * @uses get_defined_constants() to check for related error constant
     */
    protected function getErrorMessage($code)
    {
        $string = socket_strerror($code);

        $consts = get_defined_constants(true);
        $const = array_search($code, $consts['sockets'], true);
        if ($const !== false) {
            $string .= ' (' . $const . ')';
        }

        return $string;
    }

    /**
     * format given address/host/path and port
     *
     * @param string   $address
     * @param int|null $port
     * @return string
     */
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

    /**
     * format given address by splitting it into returned address and port set by reference
     *
     * @param string $address
     * @param int $port
     * @return string address with port removed
     */
    protected function unformatAddress($address, &$port)
    {
        // [::1]:2 => ::1 2
        // test:2 => test 2
        // ::1 => ::1
        // test => test

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
