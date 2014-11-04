ZF2 & Memcached
===============

Configuration
-------------
```shell
> cd config/autoload
> cp memcached.local.php.dist memcached.local.php
```

The file **memcached.local.php** is quite complex config but really there are no user-serviceble parts inside, except for 'namespace' and 'ttl' in 'caches/memcached/adapter'.

Features pre-configured:
* Doctrine cache
* Translator cache

Integration is seamless, so no need change anything in Doctrine or Translator when enabling/disabling the cache.

The service
-----------
You can use Memcached by hand:

```php
$sl = $this->getServiceLocator();
$cache = $sl->has('Memcached') ? $sl->get('Memcached') : null;

if ($cache && $cache->hasItem('test')) {
    $test = $cache->getItem('test');
} else {
    $test = 'value';
    if ($cache)
        $cache->setItem('test', $test);
}

var_dump($test);
```
