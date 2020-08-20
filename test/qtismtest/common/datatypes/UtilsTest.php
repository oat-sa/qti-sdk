<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtismtest\common\datatypes;

use PHPUnit\Framework\TestCase;
use qtism\common\datatypes\Utils;

/**
 * Tests for UtilsTest Class.
 */
class UtilsTest extends TestCase
{
    /**
     * @dataProvider integersToTest
     * @param mixed $integer integer to test
     * @param bool test result
     */
    public function testIsQtiInteger($integer, $expected)
    {
        $this->assertEquals($expected, Utils::isQtiInteger($integer));
    }

    public function integersToTest(): array
    {
        return [
            ['string', false],
            [2147483648, false],
            [-2147483648, false],
            [2147483647, true],
            [-2147483647, true],
            [2147483646, true],
            [-2147483646, true],
        ];
    }

    /**
     * @dataProvider stringsToNormalize
     */
    public function testNormalizeString($string, $normalizedString)
    {
        $this->assertEquals($normalizedString, Utils::normalizeString($string));
    }

    public function stringsToNormalize()
    {
        return [
            [
                "string with\n\rtabulations\tand\r\nnew lines\nto be replaced\rby spaces",
                'string with  tabulations and  new lines to be replaced by spaces',
            ],
            // Multi-byte
            [
                "L'élève \"\\/\"\tse lève\rénervé\n.",
                'L\'élève "\\/" se lève énervé .',
            ],
            // Multi-byte not in utf-8
            [
                iconv('utf-8', 'ISO-8859-1', "L'élève \"\\/\"\tse lève\rénervé\n."),
                iconv('utf-8', 'ISO-8859-1', 'L\'élève "\\/" se lève énervé .'),
            ],
        ];
    }
}
