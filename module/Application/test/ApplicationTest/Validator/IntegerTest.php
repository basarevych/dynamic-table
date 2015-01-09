<?php

namespace ApplicationTest\Validator;

use NumberFormatter;
use PHPUnit_Framework_TestCase;
use Application\Validator\Integer;

class IntegerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fmt = new NumberFormatter('ru_RU', NumberFormatter::DECIMAL);
    }

    public function testLocalizedInteger()
    {
        $validator = new Integer();

        $result = $validator->isValid('9 000');
        $this->assertEquals(true, $result, "Correct localized integer");
    }
}
