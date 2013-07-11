<?php

namespace qtism\data\expressions;

use \InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * Selects a random integer from the specified range [min,max] satisfying min + step * n for 
 * some integer n. For example, with min=2, max=11 and step=3 the values {2,5,8,11} are possible.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RandomInteger extends Expression {
	
	private $min = 0;
	
	private $max;
	
	private $step = 1;
	
	/**
	 * Create a new instance of RandomInteger.
	 * 
	 * @param int|string $min
	 * @param int|string $max
	 * @param int $step
	 * @throws InvalidArgumentException If $min, $max, or $step are not integers.
	 */
	public function __construct($min, $max, $step = 1) {
		$this->setMin($min);
		$this->setMax($max);
		$this->setStep($step);
	}
	
	public function getMin() {
		return $this->min;
	}
	
	public function setMin($min) {
		if (is_int($min) || Format::isVariableRef($max)) {
			$this->min = $min;
		}
		else {
			$msg = "'Min' must be an integer, '" . gettype($min) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getMax() {
		return $this->max;
	}
	
	public function setMax($max) {
		if (is_int($max) || Format::isVariableRef($max)) {
			$this->max = $max;
		}
		else {
			$msg = "'Max' must be an integer, '" . gettype($max) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getStep() {
		return $this->step;
	}
	
	public function setStep($step) {
		if (is_int($step)) {
			$this->step = $step;
		}
		else {
			$msg = "'Step' must be an integer, '" . gettype($step) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'randomInteger';
	}
}