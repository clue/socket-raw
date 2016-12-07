<?php

namespace Socket\Raw;

class Environment
{
    /**
     * Does the current runtime environment support IPv6?
     *
     * @return bool
     */
    public function supportsIPv6()
    {
        if (!$this->compiledWithIPv6Support()) {
            return false;
        }

        if ($this->runningOnTravisCI()) {
            return false;
        }

        return true;
    }

    /**
     * Does the current runtime environment support UNIX + UDG sockets?
     *
     * @return bool
     */
    public function supportsUnixSockets()
    {
        // TODO: check this check
        return defined('AF_UNIX');
    }

    /**
     * Has IPv6 support been enabled in this runtime?
     *
     * @return bool
     */
    private function compiledWithIPv6Support()
    {
        // TODO: check this check
        return defined('AF_INET6');
    }

    /**
     * Are we running on Travis CI?
     * They currently do not support IPv6 (https://blog.travis-ci.com/2015-11-27-moving-to-a-more-elastic-future)
     *
     * @return bool
     */
    private function runningOnTravisCI()
    {
        return getenv('TRAVIS') == 'true';
    }
}
