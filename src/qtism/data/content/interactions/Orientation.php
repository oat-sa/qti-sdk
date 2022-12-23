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

namespace qtism\data\content\interactions;

use qtism\common\enums\Enumeration;

/**
 * The QTI orientation enumeration.
 */
class Orientation implements Enumeration
{
    public const VERTICAL = 0;

    public const HORIZONTAL = 1;

    /**
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'VERTICAL' => 0,
            'HORIZONTAL' => 1,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower((string)$name)) {
            case 'vertical':
                return self::VERTICAL;
                break;

            case 'horizontal':
                return self::HORIZONTAL;
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
            case self::VERTICAL:
                return 'vertical';
                break;

            case self::HORIZONTAL:
                return 'horizontal';
                break;

            default:
                return false;
                break;
        }
    }
}
