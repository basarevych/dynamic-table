ZF2 Session
===========
This is a simple service for sessions support.

Configuration
-------------
**local.php**:
```php
    'session' => [
        'name'                => 'zf2skeleton',
        'save_handler'        => 'files', // 'files' or 'memcached'
        'save_path'           => realpath(__DIR__ . '/../../data/session'),
        'remember_me_seconds' => 7 * 24 * 60 * 60,
        'cookie_lifetime'     => 7 * 24 * 60 * 60,
    ],
```

Initialize your session in Application module onBootstrap:

```php
$serviceManager = $e->getApplication()->getServiceManager();
$session        = $serviceManager->get('Session');
$session->start();
```

Usage
-----
```php
$sl = $this->getServiceLocator();
$session = $sl->get('Session');
$cont = $session->getContainer();
if ($cont->offsetExists('xxx')) {
    echo "getting";
    var_dump($cont->xxx);
} else {
    echo "setting";
    $cont->xxx = 'xxx';
}
```
