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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\enums;

/**
 * Abstract enumeration automatizes the translation from constant name to
 * value and from value to name.
 */
abstract class AbstractEnumeration implements Enumeration
{
    abstract public static function asArray(): array;

    public static function getConstantByName($name)
    {
        return static::asArray()[$name] ?? false;
    }

    public static function getNameByConstant($constant)
    {
        $constants = array_flip(static::asArray());

        return $constants[$constant] ?? false;
    }
}
