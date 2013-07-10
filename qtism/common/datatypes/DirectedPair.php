<?php

namespace qtism\common\datatypes;

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
	
	
	public function equals($obj) {
		if (gettype($obj) === 'object' && $obj instanceof self) {
			return $obj->getFirst() === $this->getFirst() && $obj->getSecond() === $this->getSecond();
		}
		
		return false;
	}
}