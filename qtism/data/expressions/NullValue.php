<?php

namespace qtism\data\expressions;

/**
 * From IMS QTI:
 * 
 * null is a simple expression that returns the NULL value - the null value is 
 * treated as if it is of any desired baseType.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NullValue extends Expression {
	
	public function getQTIClassName() {
		return 'null';
	}
}