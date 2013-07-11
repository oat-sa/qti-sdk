<?php

namespace qtism\data\expressions;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * This expression, which can only be used in outcomes processing, calculates the number 
 * of items in a given sub-set, for which at least one of the defined response variables 
 * does not match its associated correctResponse. Only items for which all declared 
 * response variables have correct responses defined and have been attempted at least 
 * once are considered. The result is an integer with single cardinality.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberIncorrect extends ItemSubset {
	
	public function getQtiClassName() {
		return 'numberIncorrect';
	}
}