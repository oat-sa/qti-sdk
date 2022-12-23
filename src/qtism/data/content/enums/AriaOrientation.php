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
 * AriaOrientation Enumeration.
 *
 * Contains the possible values for the aria-orientation attribute ('horizontal', 'vertical').
 */
class AriaOrientation implements Enumeration
{
    /**
     * @var int
     */
    public const HORIZONTAL = 0;

    /**
     * @var int
     */
    public const VERTICAL = 1;

    /**
     * As Array
     *
     * Get the enumeration as an array where keys are constant names
     * and values are constant values.
     *
     * @return int[]
     */
    public static function asArray(): array
    {
        return [
            'HORIZONTAL' => self::HORIZONTAL,
            'VERTICAL' => self::VERTICAL,
        ];
    }

    /**
     * Get a constant value from the AriaOrientative enumeration by name.
     *
     * * 'horizontal' -> AriaOrientation::HORIZONTAL
     * * 'vertical' -> AriaOrientation::VERTICAL
     *
     * @param string $name
     * @return false|int The related constant value or false.
     */
    public static function getConstantByName($name)
    {
        switch (trim(strtolower($name))) {
            case 'horizontal':
                return self::HORIZONTAL;
                break;

            case 'vertical':
                return self::VERTICAL;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Get the QTI name of a AriaEnumeration value.
     *
     * @param int $constant A value from the AriaOrientation constant.
     * @return false|string
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::HORIZONTAL:
                return 'horizontal';
                break;

            case self::VERTICAL:
                return 'vertical';
                break;

            default:
                return false;
                break;
        }
    }
}
