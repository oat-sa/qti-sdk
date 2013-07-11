<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;
use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * 
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Lcm extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, -1, array(Cardinality::SINGLE, Cardinality::MULTIPLE, Cardinality::ORDERED), array(OperatorBaseType::INTEGER));
	}
	
	public function getQtiClassName() {
		return 'lcm';
	}
}