<?php
/**
 * zf2-skeleton
 *
 * @link        https://github.com/basarevych/zf2-skeleton
 * @copyright   Copyright (c) 2014-2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Doctrine;

use DateTime;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Doctrine column type to store dates in UTC
 *
 * @category    Application
 * @package     Doctrine
 */
class UtcDateTime extends DateTimeType
{
    /**
     * Before saving to DB
     *
     * @param DateTime $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null)
            return null;

        $value->setTimezone(new \DateTimeZone('UTC'));
        return $value->format($platform->getDateTimeFormatString());
    }

    /**
     * After fetching from DB
     *
     * @param string $value
     * @param AbstractPlatform $platform
     * @return DateTime
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null)
            return null;

        $val = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            new \DateTimeZone('UTC')
        );
        if (!$val)
            throw ConversionException::conversionFailed($value, $this->getName());

        $val->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $val;
    }
}
