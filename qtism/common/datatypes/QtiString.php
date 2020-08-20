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

namespace qtism\common\datatypes;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * Represents the String QTI datatype.
 */
class QtiString extends QtiScalar
{
    /**
     * Checks whether or not $value is a valid string.
     *
     * @param mixed $value
     * @throws InvalidArgumentException If $value is not a valid string.
     */
    protected function checkType($value)
    {
        if (is_string($value) !== true) {
            $msg = 'The String Datatype only accepts to store string values.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType of the value. This method systematically returns
     * the BaseType::STRING value.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return BaseType::STRING;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     *
     * @return int A value from the Cardinality enumeration.
     */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }

    /**
     * Wheter or not the current QtiString object is equal to $obj.
     *
     * Two QtiString objects are considered to be identical if their intrinsic
     * values are equals. If the current QtiString is an empty string, and $obj
     * is NULL, the values are considered equal.
     *
     * @param mixed $obj
     * @return bool
     */
    public function equals($obj)
    {
        if ($obj instanceof QtiScalar) {
            return $obj->getValue() === $this->getValue();
        } elseif ($this->getValue() === '' && $obj === null) {
            return true;
        } else {
            return $this->getValue() === $obj;
        }
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
