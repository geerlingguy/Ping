<?php

/**
 * @file
 * Tests for Ping.
 */

// TODO - Use autoloading someday.
include_once('Ping.php');
use JJG\Ping as Ping;

class PingTest extends PHPUnit_Framework_TestCase {
  private $reachable_host = 'www.google.com';
  private $unreachable_host = 'www.osdifjaosdg.com';

  public function testHost() {
    $first = $this->reachable_host;
    $ping = new Ping($first);
    $this->assertEquals($first, $ping->getHost());

    $second = 'www.apple.com';
    $ping->setHost($second);
    $this->assertEquals($second, $ping->getHost());
  }

  public function testTtl() {
    $first = 220;
    $ping = new Ping($this->reachable_host, $first);
    $this->assertEquals($first, $ping->getTtl());

    $second = 128;
    $ping->setTtl($second);
    $this->assertEquals($second, $ping->getTtl());
  }

  public function testPort() {
    $port = 2222;
    $ping = new Ping($this->reachable_host);
    $ping->setPort($port);
    $this->assertEquals($port, $ping->getPort());
  }

  public function testPingExec() {
    $ping = new Ping('www.google.com');
    $latency = $ping->ping('exec');
    $this->assertNotEquals(FALSE, $latency);

    $ping->setHost('www.sioajdsfonasdgiojsd.com');
    $latency = $ping->ping('exec');
    $this->assertEquals(FALSE, $latency);
  }

  public function testPingFsockopen() {
    $ping = new Ping($this->reachable_host);
    $latency = $ping->ping('fsockopen');
    $this->assertNotEquals(FALSE, $latency);

    $ping = new Ping($this->unreachable_host);
    $latency = $ping->ping('fsockopen');
    $this->assertEquals(FALSE, $latency);
  }

  /**
   * These tests require sudo/root so socket can be opened.
   */
  public function testPingSocket() {
    $ping = new Ping($this->reachable_host);
    $latency = $ping->ping('socket');
    $this->assertNotEquals(FALSE, $latency);

    $ping = new Ping($this->unreachable_host);
    $latency = $ping->ping('socket');
    $this->assertEquals(FALSE, $latency);
  }
}
