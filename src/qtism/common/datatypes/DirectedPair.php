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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
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

/**
 * From IMS QTI:
 * 
 * A directedPair value represents a pair of identifiers corresponding to a directed 
 * association between two objects. The two identifiers correspond to the source and 
 * destination objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DirectedPair extends Pair {
	
	/**
	 * Whether or not $obj is equal to $this. Two DirectedPair objects
	 * are considered to be equal if their first and second values are the same.
	 * 
	 * @return boolean
	 */
	public function equals($obj) {
		if (gettype($obj) === 'object' && $obj instanceof self) {
			return $obj->getFirst() === $this->getFirst() && $obj->getSecond() === $this->getSecond();
		}
		
		return false;
	}
	
	/**
	 * Get the baseType of the value. This method systematically returns the
	 * BaseType::DIRECTED_PAIR value.
	 * 
	 * @return A value from the BaseType enumeration.
	 */
	public function getBaseType() {
	    return BaseType::DIRECTED_PAIR;
	}
	
	/**
	 * Get the cardinality of the value. This method systematically returns the
	 * Cardinality::SINGLE value.
	 * 
	 * @return integer A value from the Cardinality enumeration.
	 */
	public function getCardinality() {
	    return Cardinality::SINGLE;
	}
}