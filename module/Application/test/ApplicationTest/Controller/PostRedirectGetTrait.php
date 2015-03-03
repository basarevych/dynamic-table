<?php

namespace ApplicationTest\Controller;

use Zend\Http\Request as HttpRequest;

trait PostRedirectGetTrait
{
    protected function prg($url, $postParams)
    {
        $this->dispatch($url, HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(303);

        $redirectUri = $this->getResponseHeader('Location')->getUri();
        $session = $_SESSION;
        $cookie = $_COOKIE;
        $this->reset();
        $_COOKIE = $cookie;
        $_SESSION = $session;

        $this->dispatch($redirectUri, HttpRequest::METHOD_GET);
    } 
}
