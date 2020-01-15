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
 * Represents the IntOrIdentifier QTI datatype.
 */
class QtiIntOrIdentifier extends QtiScalar
{
    /**
     * Checks whether or not $value is a valid integer or string to be
     * used as the intrinsic value of this object.
     *
     * @param mixed $value
     * @throws InvalidArgumentException If $value is not compliant with the QTI IntOrIdentifier datatype.
     */
    protected function checkType($value)
    {
        if (is_int($value) !== true && is_string($value) !== true) {
            $msg = "The IntOrIdentifier Datatype only accepts to store identifier and integer values.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType of the value. This method systematically returns
     * the BaseType::INT_OR_IDENTIFIER value.
     *
     * @return integer A value from the BaseType enumeration.
     */
    public function getBaseType()
    {
        return BaseType::INT_OR_IDENTIFIER;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     *
     * @return integer A value from the Cardinality enumeration.
     */
    public function getCardinality()
    {
        return Cardinality::SINGLE;
    }

    public function __toString()
    {
        $v = $this->getValue();
        if (is_string($v) === true) {
            return $v;
        } else {
            return '' . $v;
        }
    }
}
