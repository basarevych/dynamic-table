<?php

namespace ApplicationTest\Doctrine;

use PHPUnit_Framework_TestCase;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Application\Doctrine\UtcDateTime;

class UtcDateTimeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->var = Type::getType('utcdatetime');

        $this->platform = $this->getMockBuilder('\Doctrine\DBAL\Platforms\AbstractPlatform')
            ->disableOriginalConstructor()
            ->setMethods([ 'getDateTimeFormatString' ])
            ->getMockForAbstractClass();
        $this->platform->expects($this->any())
            ->method('getDateTimeFormatString')
            ->will($this->returnValue('Y-m-d H:i:s'));

        $this->tz = date_default_timezone_get();
        date_default_timezone_set('Europe/Kiev');
    }

    public function tearDown()
    {
        parent::tearDown();

        date_default_timezone_set($this->tz);
    }

    public function testConvertToDatabaseValue()
    {
        $this->assertEquals(
            null,
            $this->var->convertToDatabaseValue(null, $this->platform),
            "Does not return null for null value"
        );

        $date = new \DateTime('2000-01-01 15:00:00');
        $this->assertEquals(
            "2000-01-01 13:00:00",
            $this->var->convertToDatabaseValue($date, $this->platform),
            "Does not return datetime in UTC"
        );
    }

    public function testConvertToPHPValue()
    {
        $this->assertEquals(
            null,
            $this->var->convertToPHPValue(null, $this->platform),
            "Does not return null for null value"
        );

        $this->assertEquals(
            new \DateTime("2000-01-01 15:00:00"),
            $this->var->convertToPHPValue("2000-01-01 13:00:00", $this->platform),
            "Does not return datetime in local timezone"
        );
    }
}
