<?php

namespace qtism\data\expressions;

use \InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * Selects a random float from the specified range [min,max].
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RandomFloat extends Expression {
	
	private $min = 0.0;
	
	private $max;
	
	/**
	 * Create a new instance of RandomFloat.
	 * 
	 * @param number|string $min A variableRef or a float value.
	 * @param number|string $max A variableRef or a float value.
	 * @throws InvalmidArgumentException If $min or $max are not valid numerics/variableRefs.
	 */
	public function __construct($min, $max) {
		$this->setMin($min);
		$this->setMax($max);
	}
	
	/**
	 * Get the min attribute value.
	 * 
	 * @return number|string A numeric value or a variableRef.
	 */
	public function getMin() {
		return $this->min;
	}
	
	/**
	 * Set the min attribute value.
	 * 
	 * @param number|string $min A float value, int value or a variableRef.
	 * @throws InvalidArgumentException If $min is not a numeric value nor a variableRef.
	 */
	public function setMin($min) {
		if (is_numeric($min) || Format::isVariableRef($min)) {
			$this->min = $min;
		}
		else {
			$msg = "'Min' must be a numeric value or a variableRef, '" . gettype($min) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the max attribute value.
	 * 
	 * @return number|string A numeric value or a variableRef.
	 */
	public function getMax() {
		return $this->max;
	}
	
	/**
	 * Set the max attribute.
	 * 
	 * @param number|string $max A numeric value or a variableRef.
	 * @throws InvalidArgumentException If $max is not a numeric value nor a variableRef.
	 */
	public function setMax($max) {
		if (is_numeric($max) || Format::isVariableRef($max)) {
			$this->max = $max;
		}
		else {
			$msg = "'Max must be a numeric value or a variableRef, '" . gettype($max) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'randomFloat';
	}
}