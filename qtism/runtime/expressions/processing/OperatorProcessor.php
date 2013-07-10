<?php

namespace qtism\runtime\expressions\processing;

use qtism\runtime\common\RecordContainer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\data\expressions\operators\OperatorBaseType;
use qtism\data\expressions\operators\OperatorCardinality;
use qtism\data\expressions\operators\Operator;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;
use \RuntimeException;

abstract class OperatorProcessor extends ExpressionProcessor {
	
	/**
	 * A collection of QTI Runtime compliant values.
	 * 
	 * @var OperandsCollection
	 */
	private $operands;
	
	/**
	 * Create a new OperatorProcessor object.
	 * 
	 * @param Expression $expression A QTI Data Model Operator object.
	 * @param OperandsCollection $operands A collection of QTI Runtime compliant values.
	 * @throws InvalidArgumentException If $expression is not a QTI Data Model Operator object.
	 */
	public function __construct(Expression $expression, OperandsCollection $operands) {
		parent::__construct($expression);
		$this->setOperands($operands);
	}
	
	public function setExpression(Expression $expression) {
		if ($expression instanceof Operator) {
			parent::setExpression($expression);
		}
		else {
			$msg = "The OperatorProcessor class only accepts QTI Data Model Operators to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Set the collection of QTI Runtime compliant values
	 * to be used as the operands of the Operator to be processed.
	 * 
	 * @param OperandsCollection $operands A collection of QTI Runtime compliant values.
	 * @throws ExpressionProcessingException If The operands are not compliant with minimum or maximum amount of operands the operator can take.
	 */
	public function setOperands(OperandsCollection $operands) {
		// Check minimal operand count.
		$min = $this->getExpression()->getMinOperands();
		$given = count($operands);
		
		if ($given < $min) {
			$msg = "The Operator to be processed requires at least ${min} operand(s). ";
			$msg.= "${given} operand(s) given.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		// Check maximal operand count.
		$max = $this->getExpression()->getMaxOperands();
		$given = count($operands);
		
		if ($max !== -1 && $given > $max) {
			$msg = "The Operator to be processed requires at most ${max} operand(s). ";
			$msg.= "${given} operand(s) given.";
			throw new ExpressionProcessingException($msg, $this);
		}
		
		$this->operands = $operands;
	}
	
	/**
	 * Get the collection of QTI Runtime compliant values to be used
	 * as the operands of the Operator to be processed.
	 * 
	 * @return Container A collection of QTI Runtime compliant values.
	 */
	public function getOperands() {
		return $this->operands;
	}
	
	protected function throwOperandTypingError($value) {
		if (gettype($value) === 'object') {
			if ($value instanceof MultipleContainer) {
				$displayValue = 'MultipleContainer:' . BaseType::getNameByConstant($value->getBaseType());
			}
			else if ($value instanceof OrderedContainer) {
				$displayValue = 'OrderedContainer:' . BaseType::getNameByConstant($value->getBaseType());
			}
			else if ($value instanceof RecordContainer) {
				$displayValue = 'RecordContainer';
			}
			else {
				$displayValue = get_class($value);
			}
		}
		else {
			$displayValue = $value;
			if (gettype($displayValue) === 'boolean') {
				$displayValue = ($displayValue === true) ? '(bool)true' : '(bool)false';
			}
		}
		
		$className = get_class($this);
		$msg = "The operand value '${displayValue}' is not compliant with the accepted baseTypes/cardinalities of Operator '${className}'.";
		throw new ExpressionProcessingException($msg, $this);
	}
}