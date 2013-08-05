<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * responseElseIf is defined in an identical way to responseIf.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseElseIf extends QtiComponent {
	
	/**
	 * The expression to be evaluated with the Else If statement.
	 * 
	 * @var Expression
	 */
	private $expression;
	
	/**
	 * The collection of ResponseRule objects to be evaluated as sub expressions
	 * if the expression bound to the Else If statement is evaluated to true.
	 * 
	 * @var ResponseRuleCollection
	 */
	private $responseRules;
	
	/**
	 * Create a new instance of ResponseElseIf.
	 * 
	 * @param Expression $expression An expression to be evaluated with the Else If statement.
	 * @param ResponseRuleCollection $responseRules A collection of ResponseRule objects.
	 * @throws InvalidArgumentException If $responseRules is an empty collection.
	 */
	public function __construct(Expression $expression, ResponseRuleCollection $responseRules) {
		$this->setExpression($expression);
		$this->setResponseRules($responseRules);
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
	 * Get the ResponseRules to be evaluated as sub expressions if the expression bound
	 * to the Else If statement returns true.
	 * 
	 * @return ResponseRuleCollection A collection of OutcomeRule objects.
	 */
	public function getResponseRules() {
		return $this->responseRules;
	}
	
	/**
	 * Set the ResponseRules to be evaluated as sub expressions if the expression bound
	 * to the Else If statement returns true.
	 * 
	 * @param ResponseRuleCollection $responseRules A collection of ResponseRule objects.
	 * @throws InvalidArgumentException If $responseRules is an empty collection.
	 */
	public function setResponseRules(ResponseRuleCollection $responseRules) {
		if (count($responseRules) > 0) {
			$this->responseRules = $responseRules;
		}
		else {
			$msg = "A ResponseElseIf object must be bound to at lease one ResponseRule object.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'responseElseIf';
	}
	
	public function getComponents() {
		$comp = array_merge(array($this->getExpression()), $this->getResponseRules()->getArrayCopy());
		return new QtiComponentCollection($comp);
	}
}