<?php

/**
 * Ping for PHP.
 *
 * This class pings a host.
 *
 * The ping() method pings a server using 'exec', 'socket', or 'fsockopen', and
 * and returns FALSE if the server is unreachable within the given ttl/timeout,
 * or the latency in milliseconds if the server is reachable.
 *
 * Quick Start:
 * @code
 *   include 'path/to/Ping/JJG/Ping.php';
 *   use \JJG\Ping as Ping;
 *   $ping = new Ping('www.example.com');
 *   $latency = $ping->ping();
 * @endcode
 *
 * @version 1.0.0
 * @author Jeff Geerling.
 * modified by vpsmt.com
 */

namespace JJG;

class Ping {

	private $host;
	private $ttl;
	private $port = 80;
	private $ping_count = 1;
	private $max_ping_count = 5;
	private $data = 'Ping';
	private $host_type = '';
  	private $timeout = 5; //in sec


  /**
   * Called when the Ping object is created.
   *
   * @param string $host
   *   The host to be pinged.
   * @param int $ttl
   *   Time-to-live (TTL) (You may get a 'Time to live exceeded' error if this
   *   value is set too low. The TTL value indicates the scope or range in which
   *   a packet may be forwarded. By convention:
   *     - 0 = same host
   *     - 1 = same subnet
   *     - 32 = same site
   *     - 64 = same region
   *     - 128 = same continent
   *     - 255 = unrestricted
   *   
   */
  public function __construct($host = '', $ttl = 255) {

  	$this->host = $host;
  	$this->ttl = $ttl;
  	$this->host_type = 'unix';
  	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
  		$this->host_type = 'windows';
  	else if (strtoupper(PHP_OS) === 'DARWIN')
  		$this->host_type = 'mac';
  }

  /**
   * Set the ttl (in hops).
   *
   * @param int $ttl
   *   TTL in hops.
   */
  public function setTtl($ttl) {
  	$this->ttl = $ttl;
  }

    /**
   * Set the timeout value
   *
   * @param int $timeout
   * in sec
   */
    public function setTimeout($timeout) {
    	$this->timeout = intval($timeout);
    }

  /**
   * Set the host.
   *
   * @param string $host
   *   Host name or IP address.
   */
  public function setHost($host) {
  	$this->host = $host;
  }

  /**
   * Set the port (only used for fsockopen method).
   *
   * Since regular pings use ICMP and don't need to worry about the concept of
   * 'ports', this is only used for the fsockopen method, which pings servers by
   * checking port 80 (by default).
   *
   * @param int $port
   *   Port to use for fsockopen ping (defaults to 80 if not set).
   */
  public function setPort($port) {
  	$this->port = $port;
  }

  /**
   * Set the ping count
   *
   * @param int $count
   */
  public function setPingCount($count) {
  	$count = intval($count);
  	if ($count < 1) $count = 1;
  	if ($count > $this->max_ping_count) $count = $this->max_ping_count;
  	$this->ping_count = $count;
  }

  /**
   * Ping a host.
   *
   * @param string $method
   *   Method to use when pinging:
   *     - exec (default): Pings through the system ping command. Fast and
   *       robust, but a security risk if you pass through user-submitted data.
   *     - fsockopen: Pings a server on port 80.
   *     - socket: Creates a RAW network socket. Only usable in some
   *       environments, as creating a SOCK_RAW socket requires root privileges.
   *
   * @return mixed
   * return latency in milliseconds, return false if offline
   */
  public function ping($method = 'exec') {

  	$latency = false;

  	switch ($method) {
      // The exec method uses the possibly insecure exec() function, which
      // passes the input to the system. This is potentially VERY dangerous if
      // you pass in any user-submitted data. Be SURE you sanitize your inputs!
  		case 'exec':
  		$ttl = escapeshellcmd($this->ttl);
  		$host = escapeshellcmd($this->host);
        // Exec string for Windows-based systems.

  		if ($this->host_type == 'windows') {
          // -n = number of pings; -i = ttl.
  			$milli_sec = $this->timeout * 1000;
  			$timeout_string = '';
  			if ($milli_sec > 0) $timeout_string = ' -w '.$milli_sec.' ';
          $exec_string = 'ping -n '.$this->ping_count.' -i ' . $ttl . $timeout_string.' ' . $host;  //default time out is 4 seconds (ie. -w 4000)
     }
     else if ($this->host_type == 'mac') {
          // -n = numeric output; -c = number of pings; -m = ttl; -t = timeout in sec
     	$timeout_string = '';
     	if ($this->timeout > 0) $timeout_string = ' -t '.$this->timeout.' ';
     	$exec_string = 'ping -n -c '.$this->ping_count.' -m ' . $ttl . $timeout_string.' ' . $host;
     }
        // Exec string for UNIX-based systems (Linux).
     else {
          // -n = numeric output; -c = number of pings; -t = ttl; -w = deadline in sec
     	$timeout_string = '';
     	if ($this->timeout > 0) $timeout_string = ' -w '.$this->timeout.' ';
     	$exec_string = 'ping -n -c '.$this->ping_count.' -t ' . $ttl . $timeout_string.' ' . $host;
     }
     $str = exec($exec_string, $output, $return);

     print_r($output);

     if (!empty($output)) {
     	//round trip calculation (ie. avg, min, max stddev) not always return (ie. when timed out, so use latency line to get avg latency)
     	$matches = array_filter($output, array($this, 'findLatencyLine'));

     	print_r($matches);

     	$total_latency = 0;
     	if (!empty($matches)) {
     		foreach ($matches as $k=>$latency_line) {

     			$current_latency_line_array = explode(' ', $latency_line);
     			foreach ($current_latency_line_array as $item) {
     				$item = trim($item);
     				$pos = stripos($item, 'time=');
     				if ($pos !== false) {
     					$item = str_replace('time=', '', $item);
     					$item = str_replace('ms', '', $item);
     					$total_latency += floatval($item);
     					break;	//check next latency_line
     				}
     			}
              }//foreach
				
			$latency = round($total_latency / count($matches));
              
          }//latency line found
		else {
			$latency = false;
		}
      }
      else {
      	$latency = false;
      }

      break;

      // The fsockopen method simply tries to reach the host on a port. This
      // method is often the fastest, but not necessarily the most reliable.
      // Even if a host doesn't respond, fsockopen may still make a connection.
      case 'fsockopen':
      $start = microtime(true);
      $fp = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
      if (!$fp) {
      	$latency = false;
      }
      else {
      	$latency = microtime(true) - $start;
      	$latency = round($latency * 1000);
      }
      break;

      // The socket method uses raw network packet data to try sending an ICMP
      // ping packet to a server, then measures the response time. Using this
      // method requires the script to be run with root privileges, though, so
      // this method only works reliably on Windows systems and on Linux servers
      // where the script is not being run as a web user.
      case 'socket':
        // Create a package.
      $type = "\x08";
      $code = "\x00";
      $checksum = "\x00\x00";
      $identifier = "\x00\x00";
      $seq_number = "\x00\x00";
      $package = $type . $code . $checksum . $identifier . $seq_number . $this->data;

        // Calculate the checksum.
      $checksum = $this->calculateChecksum($package);

        // Finalize the package.
      $package = $type . $code . $checksum . $identifier . $seq_number . $this->data;

        // Create a socket, connect to server, then read socket and calculate.
      if ($socket = socket_create(AF_INET, SOCK_RAW, 1)) {
      	socket_connect($socket, $this->host, null);
      	$start = microtime(true);
          // Send the package.
      	socket_send($socket, $package, strlen($package), 0);
      	if (socket_read($socket, 255) !== false) {
      		$latency = microtime(true) - $start;
      		$latency = round($latency * 1000);
      	}
      	else {
      		$latency = false;
      	}
          // Close the socket.
      	socket_close($socket);
      }
      else {
      	$latency = false;
      }
        // Close the socket.
      socket_close($socket);
      break;
 }

 return $latency;
}

private function findLatencyLine($haystack) {
	$needle = 'time=';
	return(stripos($haystack, $needle));
}

  /**
   * Calculate a checksum.
   *
   * @param string $data
   *   Data for which checksum will be calculated.
   *
   * @return string
   *   Binary string checksum of $data.
   */
  private function calculateChecksum($data) {
  	if (strlen($data) % 2) {
  		$data .= "\x00";
  	}

  	$bit = unpack('n*', $data);
  	$sum = array_sum($bit);

  	while ($sum >> 16) {
  		$sum = ($sum >> 16) + ($sum & 0xffff);
  	}

  	return pack('n*', ~$sum);
  }
}
