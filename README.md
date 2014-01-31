<img src="https://github.com/geerlingguy/Ping/raw/1.x/Resources/Ping-Logo.png" alt="Ping for PHP Logo" />

# Ping

A PHP class to ping hosts.

There are a ton of different methods of pinging a server using PHP, and I've found most to be poorly documented or downright dangerous in their implementation.

Therefore, I've created this simple class, which incorporates the three most popular ping methods (`exec()` with the system's `ping` utility, `fsockopen()`, and `socket_create()`). Each method has it's benefits and drawbacks, and may work better or worse on a particular system.

Ping was created by [Jeff Geerling](http://www.lifeisaprayer.com/) of [Midwestern Mac, LLC](http://www.midwesternmac.com/) in 2012.

## Usage

This is a very simple class. Just create an instance, and run `ping()`.

```php
$host = 'www.example.com';
$ping = new Ping($host);
$latency = $ping->ping();
if ($latency !== false) {
  print 'Latency is ' . $latency . ' ms';
}
else {
  print 'Host could not be reached.';
}
```

You can also specify the ttl (maximum hops) when creating the instance:

```php
$ttl = 128;
$ping = new Ping($host, $ttl);
```

...or using the `setTtl()` method:

```php
$ping = new Ping($host);
$ping->setTtl(128);
```

You can change the host using the `setHost()` method:

```php
$ping = new Ping($host);
...
$ping->setHost('www.anotherexample.com');
```

## License

Imap is licensed under the MIT (Expat) license. See included LICENSE.md.
