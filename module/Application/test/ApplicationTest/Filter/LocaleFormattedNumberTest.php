<?php

namespace ApplicationTest\Filter;

use Locale;
use PHPUnit_Framework_TestCase;
use Application\Filter\LocaleFormattedNumber;

class LocaleFormattedNumberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        Locale::setDefault('ru_RU');
    }

    public function testLocalizedFloat()
    {
        $filter = new LocaleFormattedNumber();
        $value = $filter->filter("9 000,42");

        $this->assertEquals(9000.42, $value, "Localized float");
    }
}
