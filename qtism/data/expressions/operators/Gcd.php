<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;
use qtism\data\expressions\ExpressionCollection;

/**
 * From IMS QTI:
 * 
 * The gcd operator takes 1 or more sub-expressions which all have base-type 
 * integer and may have single, multiple or ordered cardinality. The result 
 * is a single integer equal in value to the greatest common divisor (gcd) 
 * of the argument values. If all the arguments are zero, the result is 0, 
 * gcd(0,0)=0; authors should beware of this in calculations which require 
 * division by the gcd of random values. If some, but not all, of the arguments 
 * are zero, the result is the gcd of the non-zero arguments, gcd(0,n)=n if n<>0. 
 * If any of the sub-expressions is NULL, the result is NULL. If any of the 
 * sub-expressions is not a numerical value, then the result is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Gcd extends Operator {
	
	public function __construct(ExpressionCollection $expressions) {
		parent::__construct($expressions, 1, -1, array(Cardinality::SINGLE, Cardinality::MULTIPLE, Cardinality::ORDERED), array(OperatorBaseType::INTEGER));
	}
	
	public function getQtiClassName() {
		return 'gcd';
	}
}