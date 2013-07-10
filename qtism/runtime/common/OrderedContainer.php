<?php

namespace qtism\runtime\common;

use qtism\common\enums\Cardinality;
use qtism\common\Comparable;

class OrderedContainer extends MultipleContainer {
	
	public function equals($obj) {
		$countA = count($this);
		$countB = count($obj);
		
		if (gettype($obj) === 'object' && $obj instanceof self && $countA === $countB) {
			for ($i = 0; $i < $countA; $i++) {
				$objA = $this[$i];
				$objB = $obj[$i];
				
				if (gettype($objA) === 'object' && $obj instanceof Comparable) {
					if ($objA->equals($objB) === false) {
						return false;
					}
				}
				else if (gettype($objB) === 'object' && $obj instanceof Comparable) {
					if ($objB->equals($objA) === false) {
						return false;
					}
				}
				else {
					if ($objA !== $objB) {
						return false;
					}
				}
			}
			
			return true;
		}

		return false;
	}
	
	public function getCardinality() {
		return Cardinality::ORDERED;
	}
}