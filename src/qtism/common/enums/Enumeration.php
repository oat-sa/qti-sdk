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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\enums;

/**
 * The interface to be implemented to represent an enumeration.
 */
interface Enumeration
{
    /**
     * Return the possible values of the enumeration as an array.
     *
     * @return array An associative array where keys are constant names (as they appear in the code) and values are constant values.
     */
    public static function asArray(): array;

    /**
     * Get a constant value by its name. If $name does not match any of the value
     * of the enumeration, false is returned.
     *
     * @param string $name The name of a constant of the enumeration.
     * @return int|false The value relevant to $name or false if not found.
     */
    #[\ReturnTypeWillChange]
    public static function getConstantByName($name);

    /**
     * Get a constant name by its value. If $constant does not match any of the names
     * of the enumeration, false is returned.
     *
     * @param int $constant A constant from the enumeration.
     * @return string|false The relevant name or false if not found.
     */
    #[\ReturnTypeWillChange]
    public static function getNameByConstant($constant);
}
