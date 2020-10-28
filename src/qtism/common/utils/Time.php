<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\utils;

use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * The Time class provides utility methods for time management.
 */
class Time
{
    /**
     * Get the time difference between two DateTime object in seconds.
     *
     * @param DateTime $time1
     * @param DateTime $time2
     * @return int a number of seconds.
     */
    public static function timeDiffSeconds(DateTime $time1, DateTime $time2)
    {
        $interval = $time1->diff($time2);

        return self::totalSeconds($interval);
    }

    /**
     * Get the total number of seconds a given date $interval represents.
     *
     * @param DateInterval $interval
     * @return int
     */
    public static function totalSeconds(DateInterval $interval)
    {
        $sYears = 31536000 * $interval->y;
        $sMonths = 30 * 24 * 3600 * $interval->m;
        $sDays = 24 * 3600 * $interval->d;
        $sHours = 3600 * $interval->h;
        $sMinutes = 60 * $interval->i;
        $sSeconds = $interval->s;

        $total = $sYears + $sMonths + $sDays + $sHours + $sMinutes + $sSeconds;

        return ($interval->invert === 1) ? $total * -1 : $total;
    }

    /**
     * Clone a given $time into its UTC equivalent.
     *
     * @param DateTime $time
     * @return DateTime
     */
    public static function toUtc(DateTime $time)
    {
        $newTime = clone $time;
        $newTime->setTimezone(new DateTimeZone('UTC'));

        return $newTime;
    }
}
