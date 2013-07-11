<?php

namespace qtism\data\expressions;

use \InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * Express a constant value with a given base type.
 * 
 * From IMS QTI:
 * 
 * The simplest expression returns a single value from the set defined by the given baseType.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BaseValue extends Expression {
	
	/**
	 * The baseType of the value.
	 * 
	 * @var int
	 */
	private $baseType;
	
	/**
	 * The actual value.
	 * 
	 * @var mixed
	 */
	private $value;
	
	/**
	 * Create a new instance of BaseValue.
	 * 
	 * @param int $baseType The base type of the value.
	 * @param mixed $value The actual value.
	 * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
	 */
	public function __construct($baseType, $value) {
		$this->setBaseType($baseType);
		$this->setValue($value);
	}
	
	/**
	 * Get the base type.
	 * 
	 * @return int A value from the BaseType enumeration.
	 */
	public function getBaseType() {
		return $this->baseType;
	}
	
	/**
	 * Set the base type.
	 * 
	 * @param int $baseType A value from the BaseType enumeration.
	 * @throws InvalidArgumentException If $baseType is not a value from the BaseType enumeration.
	 */
	public function setBaseType($baseType) {
		if (in_array($baseType, BaseType::asArray())) {
			$this->baseType = $baseType;
		}
		else {
			$msg = "BaseType must be a value from the BaseType enumeration.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the actual value.
	 * 
	 * @return mixed A value.
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * Set the actual value.
	 * 
	 * @param mixed $value The actual value.
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getQtiClassName() {
		return 'baseValue';
	}
}