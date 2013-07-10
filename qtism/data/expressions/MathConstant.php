<?php

namespace qtism\data\expressions;

use \InvalidArgumentException;
use qtism\data\expressions\MathEnumeration;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * From IMS QTI:
 * 
 * The result is a mathematical constant returned as a single float, e.g. π and e.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MathConstant extends Expression {
	
	/**
	 * The name of the math constant.
	 * 
	 * @var int
	 */
	private $name;
	
	/**
	 * Create a new instance of MathConstant.
	 * 
	 * @param int $value A value from the MathEnumeration enumeration.
	 */
	public function __construct($name) {
		$this->setName($name);
	}
	
	/**
	 * Get the name of the mathematical constant.
	 * 
	 * @return int A value from the MathEnumeration enumeration.
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Set the name of the math constant.
	 * 
	 * @param string $name The name of the math constant.
	 * @throws InvalidArgumentException If $name is not a valid QTI math constant name.
	 */
	public function setName($name) {
		if (in_array($name, MathEnumeration::asArray())) {
			$this->name = $name;
		}
		else {
			$msg = "${name} is not a valid QTI Math constant.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQTIClassName() {
		return 'mathConstant';
	}
}