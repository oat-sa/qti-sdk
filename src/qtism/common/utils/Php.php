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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\utils;

/**
 * Class Php
 */
class Php
{
    /**
     * Returns a displayable datatype for any $value.
     *
     * Example:
     * echo Php::displayType(null);
     * echo Php::displayType(12);
     * echo Php::displayType(12.1);
     * echo Php::displayType(new stdClass());
     *
     * // null
     * // php:integer
     * // php:double
     * // stdClass
     *
     * @param mixed $value
     * @return string
     */
    public static function displayType($value)
    {
        if ($value === null) {
            return 'null';
        } elseif (is_object($value)) {
            return get_class($value);
        } else {
            return 'php:' . gettype($value);
        }
    }
}
