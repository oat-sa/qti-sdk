<?php

namespace qtism\data\expressions;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * This expression, which can only be used in outcomes processing, calculates the number 
 * of items in a given sub-set that have been attempted (at least once) and for which a 
 * response was given. In other words, items for which at least one declared response 
 * has a value that differs from its declared default (typically NULL). The result is an 
 * integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberResponded extends ItemSubset {
	
	public function getQtiClassName() {
		return 'numberResponded';
	}
	
}