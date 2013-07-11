<?php

namespace qtism\data\expressions;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * This expression, which can only be used in outcomes processing, calculates 
 * the number of items in a given sub-set that have been selected for presentation 
 * to the candidate, regardless of whether the candidate has attempted them or not. 
 * The result is an integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberSelected extends ItemSubset {
	
	public function getQtiClassName() {
		return 'numberSelected';
	}
	
}