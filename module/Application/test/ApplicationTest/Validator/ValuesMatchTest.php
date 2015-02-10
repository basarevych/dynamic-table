<?php

namespace ApplicationTest\Validator;

use PHPUnit_Framework_TestCase;
use Application\Validator\ValuesMatch;

class ValuesMatchTest extends PHPUnit_Framework_TestCase
{
    public function testLocalizedInteger()
    {
        $validator = new ValuesMatch([ 'compareTo' => 'newPassword' ]);
        $context = [ 'newPassword' => 'foobar' ];

        $result = $validator->isValid('baz', $context);
        $this->assertEquals(false, $result, "different values reported to be the same");

        $result = $validator->isValid('foobar', $context);
        $this->assertEquals(true, $result, "Equal values do not match");
    }
}
