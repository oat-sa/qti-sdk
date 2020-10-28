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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content;

use qtism\common\enums\Enumeration;

/**
 * The Direction enumeration describes in which direction QTI content
 * is displayed. From Left to Right, From Right to Left, or Automatic
 * detection.
 */
class Direction implements Enumeration
{
    /**
     * Automatic direction detection.
     *
     * @var int
     */
    const AUTO = 0;

    /**
     * Left To Right direction.
     *
     * @var int
     */
    const LTR = 1;

    /**
     * Right to Left direction.
     *
     * @var int
     */
    const RTL = 2;

    /**
     * @return array
     */
    public static function asArray()
    {
        return [
            'AUTO' => self::AUTO,
            'LTR' => self::LTR,
            'RTL' => self::RTL,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower($name)) {
            case 'auto':
                return self::AUTO;
                break;

            case 'ltr':
                return self::LTR;
                break;

            case 'rtl':
                return self::RTL;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param false|string $constant
     * @return bool|string
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::AUTO:
                return 'auto';
                break;

            case self::LTR:
                return 'ltr';
                break;

            case self::RTL:
                return 'rtl';
                break;

            default:
                return false;
                break;
        }
    }
}
