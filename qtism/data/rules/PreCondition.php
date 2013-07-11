<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;

/**
 * A preCondition is a simple expression attached to an assessmentSection or assessmentItemRef 
 * that must evaluate to true if the item is to be presented. Pre-conditions are evaluated at 
 * the time the associated item, section or testPart is to be attempted by the candidate, 
 * during the test. They differ from rules for selection and ordering (see Test Structure) 
 * which are followed at or before the start of the test.
 * 
 * If the expression evaluates to false, or has a NULL value, the associated item or section 
 * is skipped.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PreCondition extends QtiComponent {
	
	/**
	 * The expression that will make the Precondition return true or false. 
	 * 
	 * @var Expression
	 */
	private $expression;
	
	/**
	 * Create a new instance of PreCondition.
	 * 
	 * @param Expression $expression
	 */
	public function __construct(Expression $expression) {
		$this->setExpression($expression);
	}
	
	/**
	 * Get the expression of the PreCondition.
	 * 
	 * @return Expression A QTI Expression.
	 */
	public function getExpression() {
		return $this->expression;
	}
	
	/**
	 * Set the expression of the Precondition.
	 * 
	 * @param Expression $expression A QTI Expression.
	 */
	public function setExpression(Expression $expression) {
		$this->expression = $expression;
	}
	
	public function getQtiClassName() {
		return 'preCondition';
	}
	
	public function getComponents() {
		return new QtiComponentCollection(array($this->getExpression()));
	}
}