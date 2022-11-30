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

namespace qtism\data;

use qtism\common\enums\Enumeration;

/**
 * The ShowHide enumeration.
 */
class ShowHide implements Enumeration
{
    public const SHOW = 0;

    public const HIDE = 1;

    /**
     * @return array
     */
    public static function asArray(): array
    {
        return [
            'SHOW' => self::SHOW,
            'HIDE' => self::HIDE,
        ];
    }

    /**
     * @param false|int $name
     * @return bool|int
     */
    public static function getConstantByName($name)
    {
        switch (strtolower((string)$name)) {
            case 'show':
                return self::SHOW;
                break;

            case 'hide':
                return self::HIDE;
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
            case self::SHOW:
                return 'show';
                break;

            case self::HIDE:
                return 'hide';
                break;

            default:
                return false;
                break;
        }
    }
}
