<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * The containerSize operator takes a sub-expression with any base-type and either 
 * multiple or ordered cardinality. The result is an integer giving the number of 
 * values in the sub-expression, in other words, the size of the container. If 
 * the sub-expression is NULL the result is 0. This operator can be used for 
 * determining how many choices were selected in a multiple-response 
 * choiceInteraction, for example.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ContainerSize extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, 1, array(Cardinality::MULTIPLE, Cardinality::ORDERED), array(OperatorBaseType::ANY));
	}
	
	public function getQTIClassName() {
		return 'containerSize';
	}
}