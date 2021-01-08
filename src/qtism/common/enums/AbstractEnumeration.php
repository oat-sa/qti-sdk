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

use InvalidArgumentException;

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

    /**
     * Returns the default constant for the enumeration.
     *
     * @return int|null
     */
    abstract public static function getDefault(): ?int;

    /**
     * Checks that the given value is null or one of the enumeration constants.
     *
     * @param int|string|null $value
     * @param string $argumentName
     * @return int|null
     * @throws InvalidArgumentException when $value is not in the enumeration.
     */
    public static function accept($value, string $argumentName): ?int
    {
        if ($value !== null
            && !array_key_exists($value, static::asArray())
            && !in_array($value, static::asArray(), true)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "%s" argument must be a value from the %s enumeration, "%s" given.',
                    $argumentName,
                    basename(str_replace('\\', '/', static::class)),
                    $value
                )
            );
        }
        
        return $value ?? static::getDefault();
    }
}
