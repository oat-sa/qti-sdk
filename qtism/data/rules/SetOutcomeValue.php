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
 * The setOutcomeValue rule sets the value of an outcome variable to the value obtained from the 
 * associated expression. An outcome variable can be updated with reference to a previously 
 * assigned value, in other words, the outcome variable being set may appear in the expression 
 * where it takes the value previously assigned to it.
 * 
 * Special care is required when using the numeric base-types because floating point values 
 * can not be assigned to integer variables and vice-versa. The truncate, round or 
 * integerToFloat operators must be used to achieve numeric type conversion.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SetOutcomeValue extends QtiComponent implements OutcomeRule, ResponseRule {
	
	/**
	 * From IMS QTI:
	 * 
	 * The outcome variable to set.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * From IMS QTI:
	 * 
	 * An expression which must have an effective baseType and cardinality that matches 
	 * the base-type and cardinality of the outcome variable being set.
	 * 
	 * @var Expression
	 */
	private $expression;
	
	/**
	 * Create a new instance of SetOutcomeValue.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @param Expression $expression A QTI Expression.
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
	 * Set the identifier of the oucome variable to set.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function setIdentifier($identifier) {
		if (Format::isIdentifier($identifier, false)) {
			$this->identifier = $identifier;
		}
		else {
			$msg = "Identifier must be a valid QTI Identifier.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Set the expression used to set the variable targeted by the identifier attribute.
	 * 
	 * @param Expression $expression A QTI Expression.
	 */
	public function setExpression(Expression $expression) {
		$this->expression = $expression;
	}
	
	/**
	 * Get the expression used to set the variabled targeted by the identifier attribute.
	 * 
	 * @return Expression
	 */
	public function getExpression() {
		return $this->expression;
	}
	
	public function getQtiClassName() {
		return 'setOutcomeValue';
	}
	
	public function getComponents() {
		return new QtiComponentCollection(array($this->getExpression()));
	}
}