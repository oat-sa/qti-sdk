<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The match operator takes two sub-expressions which must both have the same base-type 
 * and cardinality. The result is a single boolean with a value of true if the two 
 * expressions represent the same value and false if they do not. If either 
 * sub-expression is NULL then the operator results in NULL.
 * 
 * The match operator must not be confused with broader notions of equality such as 
 * numerical equality. To avoid confusion, the match operator should not be used to 
 * compare subexpressions with base-types of float and must not be used on 
 * sub-expressions with a base-type of duration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Match extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SAME), array(OperatorCardinality::SAME));
	}
	
	public function getQtiClassName() {
		return 'match';
	}
}