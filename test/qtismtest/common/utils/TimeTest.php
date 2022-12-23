<?php

namespace qtismtest\common\utils;

use DateTime;
use DateTimeZone;
use Exception;
use qtism\common\utils\Time as TimeUtils;
use qtismtest\QtiSmTestCase;

/**
 * Class TimeTest
 */
class TimeTest extends QtiSmTestCase
{
    /**
     * @dataProvider timeDiffSecondsProvider
     *
     * @param DateTime $time1
     * @param DateTime $time2
     * @param int $expectedSeconds
     */
    public function testTimeDiffSeconds(DateTime $time1, DateTime $time2, $expectedSeconds): void
    {
        $this::assertSame($expectedSeconds, TimeUtils::timeDiffSeconds($time1, $time2));
    }

    public function testBasicToUtc(): void
    {
        $originalTime = DateTime::createFromFormat('Y-m-d G:i:s', '2014-07-15 16:56:20', new DateTimeZone('Europe/Luxembourg'));
        $utcTime = TimeUtils::toUtc($originalTime);
        $this::assertEquals('2014-07-15 14:56:20', $utcTime->format('Y-m-d H:i:s'));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function timeDiffSecondsProvider(): array
    {
        $tz = new DateTimeZone('UTC');

        return [
            [new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:00:01+00:00', $tz), 1],
            [new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:01:00+00:00', $tz), 60],
            [new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T12:59:59+00:00', $tz), -1],
            [new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:00:00+00:00', $tz), 0],
        ];
    }
}
