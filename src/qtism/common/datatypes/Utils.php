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
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\datatypes;

/**
 * A class focusing on providing utility methods
 * for QTI Datatypes handling.
 */
class Utils
{
    /**
     * Whether a given $integer value is a QTI compliant
     * integer in the [-2147483647, 2147483647] range.
     *
     * @param mixed $integer the value to test
     * @return bool
     */
    public static function isQtiInteger($integer): bool
    {
        // QTI integers are twos-complement 32-bits integers.
        return is_int($integer)
            && $integer <= 2147483647
            && $integer >= -2147483647;
    }

    /**
     * Normalizes a string in the sense of xs:simpleType normalizedString with
     * whiteSpace constraint as replace.
     * See http://www.w3.org/TR/xmlschema-2/#normalizedString
     *
     * @param string $string the string to normalize
     * @return string
     */
    public static function normalizeString(string $string): string
    {
        return str_replace(["\n", "\r", "\t"], ' ', $string);
    }
}
