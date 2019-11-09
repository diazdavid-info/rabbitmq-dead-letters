<?php

namespace Diaz\Shared\Infrastructure\RabbitMQ;

/**
 * Class RabbitMQConnection
 * @package Diaz\Shared\Infrastructure\RabbitMQ
 */
class RabbitMQConnection
{
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $user;
    /** @var string */
    private $pass;
    /** @var string */
    private $vHosts;

    /**
     * RabbitMQConnexion constructor.
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $vHosts
     */
    public function __construct($host, $port, $user, $pass, $vHosts)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->vHosts = $vHosts;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getVHosts()
    {
        return $this->vHosts;
    }
}
