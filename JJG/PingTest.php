<?php

/**
 * @file
 * Tests for Ping.
 */

use PHPUnit\Framework\TestCase;

// TODO - Use autoloading someday.
include_once('Ping.php');
use JJG\Ping as Ping;

class PingTest extends TestCase {
  private $reachable_host = 'www.google.com';
  private $unreachable_host = '254.254.254.254';
  private $low_latency_host = '127.0.0.1';

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

  public function testTimeout() {
    $timeout = 5;
    $startTime = microtime(true);
    $ping = new Ping($this->unreachable_host, 255, $timeout);
    $ping->ping('exec');
    $time = floor(microtime(true) - $startTime);
    $this->assertEquals($timeout, $time);
  }

  public function testLowLatencyHost() {
    $low_latency = $this->low_latency_host;
    $ping = new Ping($low_latency);
    $ping->ping('exec');
    $latency = $ping->ping();
    $this->assertGreaterThan(0, $latency);
  }

  public function testPort() {
    $port = 2222;
    $ping = new Ping($this->reachable_host);
    $ping->setPort($port);
    $this->assertEquals($port, $ping->getPort());
  }

  public function testGetCommandOutput() {
    $ping = new Ping('127.0.0.1');
    $latency = $ping->ping('exec');
    $this->assertNotNull($ping->getCommandOutput());
  }

  public function testIpAddress() {
    $ping = new Ping('127.0.0.1');
    $latency = $ping->ping('exec');
    $this->assertEquals('127.0.0.1', $ping->getIpAddress());
  }

  public function testPingExec() {
    $ping = new Ping($this->reachable_host);
    $latency = $ping->ping('exec');
    $this->assertNotEquals(FALSE, $latency);

    $ping->setHost($this->unreachable_host);
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
