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

namespace qtism\data\content\enums;

use qtism\common\enums\Enumeration;

/**
 * AriaLive Enumeration.
 *
 * Contains the possible values for the aria-live attribute ('off', 'polite', 'assertive').
 */
class AriaLive implements Enumeration
{
    /**
     * @var int
     */
    const OFF = 0;

    /**
     * @var int
     */
    const POLITE = 1;

    /**
     * @var int
     */
    const ASSERTIVE = 2;

    /**
     * As Array
     *
     * Get the enumeration as an array where keys are constant names
     * and values are constant values.
     *
     * @return int[]
     */
    public static function asArray()
    {
        return [
            'OFF' => self::OFF,
            'POLITE' => self::POLITE,
            'ASSERTIVE' => self::ASSERTIVE,
        ];
    }

    /**
     * Get a constant value from the AriaLive enumeration by name.
     *
     * * 'off' -> AriaLive::OFF
     * * 'polite' -> AriaLive::POLITE
     * * 'assertive' -> ArioLive::ASSERTIVE
     *
     * @param string $name
     * @return false|int The related constant value or false.
     */
    public static function getConstantByName($name)
    {
        switch (trim(strtolower($name))) {
            case 'off':
                return self::OFF;
                break;

            case 'polite':
                return self::POLITE;
                break;

            case 'assertive':
                return self::ASSERTIVE;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Get the QTI name of a AriaEnumeration value.
     *
     * @param int $constant A value from the AriaLive constant.
     * @return false|string
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::OFF:
                return 'off';
                break;

            case self::POLITE:
                return 'polite';
                break;

            case self::ASSERTIVE:
                return 'assertive';
                break;

            default:
                return false;
                break;
        }
    }
}
