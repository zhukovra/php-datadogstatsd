<?php

namespace DataDog\Tests;

use DataDog\DogStatsd;
use PHPUnit\Framework\TestCase;

class UdpTest extends TestCase
{
    /**
     * @var resource Listenging socket
     */
    private $sock;

    /**
     * @var DogStatsd Sending client
     */
    private $datadog;

    protected function setUp()
    {
        $this->datadog = new DogStatsd();
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        //   Bind the socket to an address/port
        if (!socket_bind($this->sock, 'localhost', 8125)) {
            throw new \RuntimeException('Could not bind socket to address');
        }

        // Start listening for connections
        socket_set_nonblock($this->sock);
    }

    protected function tearDown()
    {
        // clean socket
        $this->sock = null;
    }

    private function read()
    {
        // Read the input from the client &#8211; 1024 bytes
        return socket_read($this->sock, 1024);
    }

    public function testCounter()
    {
        $this->datadog->increment('counter');
        $this->assertEquals('counter:1|c', $this->read());
    }

    public function testGauge()
    {
        $this->datadog->gauge('gauge', 0.1);
        $this->assertEquals('gauge:0.1|g', $this->read());
    }

    public function testHistogram()
    {
        $this->datadog->histogram('histogram', 0.1);
        $this->assertEquals('histogram:0.1|h', $this->read());
    }

    public function testTiming()
    {
        $this->datadog->timing('timing', 1.5);
        $this->assertEquals('timing:1.5|ms', $this->read());
    }
}
