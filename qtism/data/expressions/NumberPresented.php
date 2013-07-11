<?php

namespace qtism\data\expressions;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * This expression, which can only be used in outcomes processing, calculates the 
 * number of items in a given sub-set that have been attempted (at least once). 
 * In other words, items with which the user has interacted, whether or not they 
 * provided a response. The result is an integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberPresented extends ItemSubset {
	
	public function getQtiClassName() {
		return 'numberPresented';
	}
	
}