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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * 
 *
 */

namespace qtism\common\datatypes;

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use \InvalidArgumentException;

/**
 * Represents the String QTI datatype.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class String extends Scalar implements QtiDatatype {
    
    /**
     * Checks whether or not $value is a valid string.
     * 
     * @throws \InvalidArgumentException If $value is not a valid string.
     */
    protected function checkType($value) {
        if (is_string($value) !== true) {
            $msg = "The String Datatype only accepts to store string values.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the baseType of the value. This method systematically returns
     * the BaseType::STRING value.
     * 
     * @return A value from the BaseType enumeration.
     */
    public function getBaseType() {
        return BaseType::STRING;
    }
    
    /**
     * Get the cardinality of the value. This method systematically returns
     * the Cardinality::SINGLE value.
     * 
     * @return A value from the Cardinality enumeration.
     */
    public function getCardinality() {
        return Cardinality::SINGLE;
    }
    
    public function __toString() {
        return $this->getValue();
    }
}