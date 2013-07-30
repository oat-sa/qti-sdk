<?php

namespace qtism\runtime\expressions;

use qtism\runtime\common\Processable;
use qtism\runtime\common\State;
use qtism\data\expressions\Expression;

/**
 * The ExpressionProcessor class aims at processing QTI Data Model
 * Expressions.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class ExpressionProcessor implements Processable {
	
	/**
	 * The QTI Data Model expression to be Processed.
	 * 
	 * @var Expression
	 */
	private $expression = null;
	
	/**
	 * A state.
	 * 
	 * @var State
	 */
	private $state = null;
	
	/**
	 * Create a new ExpressionProcessor object.
	 * 
	 * @param Expression $expression The QTI Data Model Expression to be processed.
	 */
	public function __construct(Expression $expression) {
		$this->setExpression($expression);
		$this->setState(new State());
	}
	
	/**
	 * Set the QTI Data Model Expression to be processed.
	 * 
	 * @param Expression $expression A QTI Data Model Expression object.
	 */
	public function setExpression(Expression $expression) {
		$this->expression = $expression;
	}
	
	/**
	 * Get the QTI Data Model Expression to be processed.
	 * 
	 * @return Expression A QTI Data Model Expression object.
	 */
	public function getExpression() {
		return $this->expression;
	}
	
	/**
	 * Set the current State object.
	 * 
	 * @param State $state A State object.
	 */
	public function setState(State $state) {
		$this->state = $state;
	}
	
	/**
	 * Get the current State object.
	 * 
	 * @return State
	 */
	public function getState() {
		return $this->state;
	}
	
	/**
	 * Implementations of ExpressionProcessor must include their processing
	 * business logic in this method in order to return a QTI Runtime compliant
	 * value reflecting the logic expressed by the QTI Data Model Expression
	 * to be processed.
	 * 
	 * @return mixed A QTI Runtime compliant value.
	 * @throws ExpressionProcessingException If something wrong occurs during processing.
	 */
	abstract public function process();
}