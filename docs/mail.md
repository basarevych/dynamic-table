ZF2 Mail Service
================

This is really simple service for sending mail using ZF2.

Configuration
-------------
You should have in your **config/autoload/local.php**:

```php
    'mail' => [
        'transport' => 'sendmail'
    ],
```

Or:

```php
    'mail' => [
        'transport' => 'smtp',
        'host'      => '127.0.0.1',
        'port'      => 25
    ],
```

**sendmail** transport uses PHP functions to send email, **smtp** transport talks directly to mail server.

Sending mail example
--------------------
In order to send HTML Unicode mail from your controller:

```php
$mail = $this->getServiceLocator()->get('Mail');

$html = "<h3>Test HTML / Юникод</h3>";

$msg = $mail->createHtmlMessage($html);
$msg->setFrom("root@example.com", "Charlie Root");
$msg->setTo("person@gmail.com", "Some Person");
$msg->setSubject("the subject / юникод");

$mail->getTransport()->send($msg);
```

**NOTE**: Your mail server might impose restrictions or not even accept mail for some From: header values. Use the real one.
