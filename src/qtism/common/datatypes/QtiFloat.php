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
 * Represents the QTI Float datatype.
 */
class QtiFloat extends QtiScalar
{
    /**
     * Check whether $value is a Float object.
     *
     * @param mixed $value
     * @throws InvalidArgumentException If $value is not an instance of Float.
     */
    protected function checkType($value): void
    {
        if (!is_float($value)) {
            $msg = 'The Float Datatype only accepts to store float values.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the baseType of the value. This method systematically returns
     * the BaseType::FLOAT value.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType(): int
    {
        return BaseType::FLOAT;
    }

    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     *
     * @return int A value from the Cardinality enumeration.
     */
    public function getCardinality(): int
    {
        return Cardinality::SINGLE;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getValue();
    }
}
