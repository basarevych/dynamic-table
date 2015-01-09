<?php

namespace ApplicationTest\Validator;

use NumberFormatter;
use PHPUnit_Framework_TestCase;
use Application\Validator\Float;

class FloatTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fmt = new NumberFormatter('ru_RU', NumberFormatter::DECIMAL);
    }

    public function testLocalizedFloat()
    {
        $validator = new Float();

        $result = $validator->isValid('9 000,42');
        $this->assertEquals(true, $result, "Correct localized float");
    }
}
