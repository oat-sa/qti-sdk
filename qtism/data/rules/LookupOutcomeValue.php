<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\expressions\Expression;
use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The lookupOutcomeValue rule sets the value of an outcome variable to the value obtained 
 * by looking up the value of the associated expression in the lookupTable associated with 
 * the outcome's declaration.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class LookupOutcomeValue extends QtiComponent implements OutcomeRule, ResponseRule {
	
	/**
	 * The identifier of the outcome variable to set.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * From IMS QTI:
	 * 
	 * An expression which must have single cardinality and an effective baseType of 
	 * either integer, float or duration. Integer type is required when the associated 
	 * table is a matchTable.
	 * 
	 * @var Expression
	 */
	private $expression;
	
	/**
	 * Create a new instance of LookupOutcomeValue.
	 * 
	 * @param string $identifier The identifier of the outcome variable to set.
	 * @param Expression $expression An expression which must have single cardinality and an effective baseType of either integer, float or duration.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function __construct($identifier, Expression $expression) {
		$this->setIdentifier($identifier);
		$this->setExpression($expression);
	}
	
	/**
	 * Get the identifier of the outcome variable to set.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * Set the identifier of the outcome variable to set.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function setIdentifier($identifier) {
		if (Format::isIdentifier($identifier)) {
			$this->identifier = $identifier;
		}
		else {
			$msg = "Identifier must be a vali QTI Identifier.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the expression.
	 * 
	 * @return Expression A QTI Expression object.
	 */
	public function getExpression() {
		return $this->expression;
	}
	
	/**
	 * Set the expression.
	 * 
	 * @param Expression $expression A QTI Expression object.
	 */
	public function setExpression(Expression $expression) {
		$this->expression = $expression;
	}
	
	public function getQtiClassName() {
		return 'lookupOutcomeValue';
	}
	
	public function getComponents() {
		$comp = array($this->getExpression());
		return new QtiComponentCollection($comp);
	}
}