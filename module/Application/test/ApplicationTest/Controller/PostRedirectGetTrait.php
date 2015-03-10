<?php

namespace ApplicationTest\Controller;

use Zend\Http\Request as HttpRequest;

trait PostRedirectGetTrait
{
    protected function prg($url, $postParams, $init = null)
    {
        $this->dispatch($url, HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(303);

        $session = $_SESSION;
        $cookie = $_COOKIE;
        $this->reset();
        $this->setUp();
        $_COOKIE = $cookie;
        $_SESSION = $session;


        $this->dispatch($url, HttpRequest::METHOD_GET);
    } 
}
