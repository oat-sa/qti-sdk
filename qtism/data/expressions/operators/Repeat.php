<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;
use qtism\common\utils\Format;
use qtism\data\expressions\ExpressionCollection;
use \InvalidArgumentException;

/**
 * The repeat operator takes 1 or more sub-expressions, all of which must have either 
 * single or ordered cardinality and the same baseType.
 * 
 * The result is an ordered container having the same baseType as its sub-expressions.
 * The container is filled sequentially by evaluating each sub-expression in turn and 
 * adding the resulting single values to the container, iterating this process 
 * numberRepeats times in total. If numberRepeats refers to a variable whose 
 * value is less than 1, the value of the whole expression is NULL.
 * 
 * Any sub-expressions evaluating to NULL are ignored. If all sub-expressions 
 * are NULL then the result is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Repeat extends Operator {
	
	/**
	 * A number of repetitions or a variable reference.
	 * 
	 * @var integer|string
	 */
	private $numberRepeats;
	
	/**
	 * Create a new instance of Repeat.
	 * 
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param integer $numberRepeats An integer or a QTI variable reference.
	 */
	public function __construct(ExpressionCollection $expressions, $numberRepeats) {
		parent::__construct($expressions, 1, -1, array(Cardinality::SINGLE, Cardinality::ORDERED), array(OperatorBaseType::SAME));
		$this->setNumberRepeats($numberRepeats);
	}
	
	/**
	 * Set the numberRepeats attribute.
	 * 
	 * @param integer|string $numberRepeats An integer or a QTI variable reference.
	 * @throws InvalidArgumentException If $numberRepeats is not an integer nor a valid QTI variable reference.
	 */
	public function setNumberRepeats($numberRepeats) {
		if (is_int($numberRepeats) || (is_string($numberRepeats) && Format::isVariableRef($numberRepeats))) {
			$this->numberRepeats = $numberRepeats;
		}
		else {
			$msg = "The numberRepeats argument must be an integer or a variable reference, '" . gettype($numberRepeats) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the numberRepeats attribute.
	 * 
	 * @return integer|string An integer or a QTI variable reference.
	 */
	public function getNumberRepeats() {
		return $this->numberRepeats;
	}
	
	public function getQtiClassName() {
		return 'repeat';
	}
}