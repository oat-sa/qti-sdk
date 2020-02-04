<?php

namespace qtismtest\common\utils;

use qtismtest\QtiSmTestCase;
use qtism\common\utils\Time as TimeUtils;
use DateTime;
use DateTimeZone;

class TimeTest extends QtiSmTestCase
{
    
    /**
     * @dataProvider timeDiffSecondsProvider
     *
     * @param DateTime $time1
     * @param DateTime $time2
     * @param integer $expectedSeconds
     */
    public function testTimeDiffSeconds(DateTime $time1, DateTime $time2, $expectedSeconds)
    {
        $this->assertSame($expectedSeconds, TimeUtils::timeDiffSeconds($time1, $time2));
    }
    
    public function testBasicToUtc()
    {
        $originalTime = DateTime::createFromFormat('Y-m-d G:i:s', '2014-07-15 16:56:20', new DateTimeZone('Europe/Luxembourg'));
        $utcTime = TimeUtils::toUtc($originalTime);
        $this->assertEquals('2014-07-15 14:56:20', $utcTime->format('Y-m-d H:i:s'));
    }
    
    public function timeDiffSecondsProvider()
    {
        $tz = new DateTimeZone('UTC');
        
        return array(
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:00:01+00:00', $tz), 1),
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:01:00+00:00', $tz), 60),
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T12:59:59+00:00', $tz), -1),
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:00:00+00:00', $tz), 0)
        );
    }
}
