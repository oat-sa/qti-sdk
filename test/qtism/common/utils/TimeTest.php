<?php

use qtism\common\utils\Time as TimeUtils;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class TimeTest extends QtiSmTestCase {
    
    /**
     * @dataProvider timeDiffSecondsProvider
     * 
     * @param DateTime $time1
     * @param DateTime $time2
     * @param integer $expectedSeconds
     */
    public function testTimeDiffSeconds(DateTime $time1, DateTime $time2, $expectedSeconds) {
        $this->assertSame($expectedSeconds, TimeUtils::timeDiffSeconds($time1, $time2));
    }
    
    public function timeDiffSecondsProvider() {
        $tz = new DateTimeZone('UTC');
        
        return array(
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:00:01+00:00', $tz), 1),
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:01:00+00:00', $tz), 60),
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T12:59:59+00:00', $tz), -1),
            array(new DateTime('2005-08-15T13:00:00+00:00', $tz), new DateTime('2005-08-15T13:00:00+00:00', $tz), 0)
        );
    }
}