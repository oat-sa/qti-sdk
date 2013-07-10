<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The truncate operator takes a single sub-expression which must have single
 * cardinality and a numerical base-type. The result is a value of base-type
 * integer formed by truncating the value of the sub-expression towards zero.
 * For example, the value 6.8 becomes 6 and the value -6.8 becomes -6. If the
 * sub-expression is NULL then the operator results in NULL. If the sub-expression
 * is NaN, then the result is NULL. If the sub-expression is INF, then the result
 * is INF. If the sub-expression is -INF, then the result is -INF.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Truncate extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::SINGLE), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));
	}
	
	public function getQTIClassName() {
		return 'truncate';
	}
}