<?php

namespace qtism\data\expressions;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * This expression, which can only be used in outcomes processing, calculates 
 * the number of items in a given sub-set, for which the all defined response 
 * variables match their associated correctResponse. Only items for which all 
 * declared response variables have correct responses defined are considered. 
 * The result is an integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberCorrect extends ItemSubset {
	
	public function getQtiClassName() {
		return 'numberCorrect';
	}
}