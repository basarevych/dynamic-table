ZF2 Error Strategy
==================
The framework comes with two error strategies: ExceptionStrategy and RouteNotFoundStrategy. They are both quite simple and just print the error.

This ErrorStrategy class is a replacement for both of them (it unregisters that strategies).

The main idea here is to turn everything into an exception. It handles the exception in the following way:

* **Application\Exception\HttpException** uses its **code** parameter as the resulting HTTP error

  Subclasses:

    * **Application\Exception\BadRequestException** - 400 Bad Request
    * **Application\Exception\UnauthorizedException** - 401 Unauthorized
    * **Application\Exception\AccessDeniedException** - 403 Forbidden
    * **Application\Exception\NotFoundException** - 404 Not Found
    * **Application\Exception\NotImplementedException** - 405 Not Implemented

* Any other exception returns error 500 (Internal server Error)

So if you need to return a specific error from your controller, for example, you can just throw an exception:

```php
// Produces 404 Not Found:
throw new \Application\Exception\HttpException('The message', 404);

// The same result:
throw new \Application\Exception\NotFoundException('The message');
```

The other useful feature is emailing the exception to predefined address.

Configuration
-------------
```php
    'exceptions' => [
        'display'       => true,    // Display exception details?
        'forward' => [
            'enabled'   => false,   // Forward exception via email?
            'codes'     => [ 500 ],
            'from'      => "root@localhost",        // Use real one
            'to'        => "tester@example.com",    // Who will receive report
            'subject'   => "MyProject Exception"    // The project name
        ]
    ],
```

**'display'** parameter turn on/off printing of exception details to the user.

**'forward'** section setup reporting the exception via email. Set **'enabled'** to **true** and list the exception codes you want to be informed of in **'codes'** parameter (Error 500 by default only).
