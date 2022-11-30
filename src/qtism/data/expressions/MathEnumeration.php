<?php

declare(strict_types=1);

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

namespace qtism\data\expressions;

use qtism\common\enums\Enumeration;

/**
 * The class of Mathematical constants provided by QTI.
 */
class MathEnumeration implements Enumeration
{
    /**
     * From IMS QTI:
     *
     * The number π, the ratio of the circumference of a circle to its diameter.
     *
     * @var float
     */
    public const PI = 0;

    /**
     * From IMS QTI:
     *
     * The number e, exp(1).
     *
     * @var float
     */
    public const E = 1;

    /**
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'PI' => self::PI,
            'E' => self::E,
        ];
    }

    /**
     * @param false|string $constant
     * @return bool|string
     */
    public static function getNameByConstant($constant)
    {
        switch ($constant) {
            case self::PI:
                return 'pi';
                break;

            case self::E:
                return 'e';
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param false|int $name
     * @return bool|float
     */
    public static function getConstantByName($name)
    {
        switch (strtolower((string)$name)) {
            case 'pi':
                return self::PI;
                break;

            case 'e':
                return self::E;
                break;

            default:
                return false;
                break;
        }
    }
}
