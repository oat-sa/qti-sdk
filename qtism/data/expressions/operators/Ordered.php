<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * The ordered operator takes 0 or more sub-expressions all of which must have 
 * either single or ordered cardinality. Although the sub-expressions may be of 
 * any base-type they must all be of the same base-type. The result is a container 
 * with ordered cardinality containing the values of the sub-expressions, 
 * sub-expressions with ordered cardinality have their individual values 
 * added (in order) to the result: contains cannot contain other containers. 
 * For example, when applied to A, B, {C,D} the ordered operator results 
 * in {A,B,C,D}. Note that the ordered operator never results in an empty 
 * container. All sub-expressions with NULL values are ignored. If no 
 * sub-expressions are given (or all are NULL) then the result is NULL
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Ordered extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 0, -1, array(Cardinality::SINGLE, Cardinality::ORDERED), array(OperatorBaseType::SAME));
	}
	
	public function getQtiClassName() {
		return 'ordered';
	}
}