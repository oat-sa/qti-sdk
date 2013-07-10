<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;

use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * outcomeElseIf is defined in an identical way to outcomeIf.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeElseIf extends QtiComponent {
	
	/**
	 * The expression to be evaluated with the Else If statement.
	 * 
	 * @var Expression
	 */
	private $expression;
	
	/**
	 * The collection of OutcomRule objects to be evaluated as sub expressions
	 * if the expression bound to the Else If statement is evaluated to true.
	 * 
	 * @var OutcomeRuleCollection
	 */
	private $outcomeRules;
	
	/**
	 * Create a new instance of OutcomeElseIf.
	 * 
	 * @param Expression $expression An expression to be evaluated with the Else If statement.
	 * @param OutcomeRuleCollection $outcomeRules A collection of OutcomeRule objects.
	 * @throws InvalidArgumentException If $outcomeRules is an empty collection.
	 */
	public function __construct(Expression $expression, OutcomeRuleCollection $outcomeRules) {
		$this->setExpression($expression);
		$this->setOutcomeRules($outcomeRules);
	}
	
	/**
	 * Get the expression to be evaluated with the Else If statement.
	 * 
	 * @return Expression An Expression object.
	 */
	public function getExpression() {
		return $this->expression;
	}
	
	/**
	 * Set the expression to be evaluated with the Else If statement.
	 * 
	 * @param Expression $expression An Expression object.
	 */
	public function setExpression(Expression $expression) {
		$this->expression = $expression;
	}
	
	/**
	 * Get the OutcomeRules to be evaluated as sub expressions if the expression bound
	 * to the Else If statement returns true.
	 * 
	 * @return OutcomeRuleCollection A collection of OutcomeRule objects.
	 */
	public function getOutcomeRules() {
		return $this->outcomeRules;
	}
	
	/**
	 * Set the OutcomeRules to be evaluated as sub expressions if the expression bound
	 * to the Else If statement returns true.
	 * 
	 * @param OutcomeRuleCollection $outcomeRules A collection of OutcomeRule objects.
	 * @throws InvalidArgumentException If $outcomeRules is an empty collection.
	 */
	public function setOutcomeRules(OutcomeRuleCollection $outcomeRules) {
		if (count($outcomeRules) > 0) {
			$this->outcomeRules = $outcomeRules;
		}
		else {
			$msg = "An OutcomeElseIf object must be bound to at lease one OutcomeRule object.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQTIClassName() {
		return 'outcomeElseIf';
	}
	
	public function getComponents() {
		$comp = array_merge(array($this->getExpression()), $this->getOutcomeRules()->getArrayCopy());
		return new QtiComponentCollection($comp);
	}
}