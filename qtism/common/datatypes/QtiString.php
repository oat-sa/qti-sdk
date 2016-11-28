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
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * 
 *
 */

namespace qtism\common\datatypes;

use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use \InvalidArgumentException;

class QtiString extends QtiScalar implements QtiDatatype {
    
    protected function checkType($value) {
        if (is_string($value) !== true) {
            $msg = "The String Datatype only accepts to store string values.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    public function getBaseType() {
        return BaseType::STRING;
    }
    
    public function getCardinality() {
        return Cardinality::SINGLE;
    }
    
    /**
     * Wheter or not the current QtiString object is equal to $obj. 
     * 
     * Two QtiString objects are considered to be identical if their intrinsic
     * values are equals. If the current QtiString is an empty string, and $obj
     * is NULL, the values are considered equal.
     *
     * @return boolean
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
    
    public function __toString() {
        return $this->getValue();
    }
}
