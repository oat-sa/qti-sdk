<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The patternMatch operator takes a sub-expression which must have single cardinality
 * and a base-type of string. The result is a single boolean with a value of true if
 * the sub-expression matches the regular expression given by pattern and false if it
 * doesn't. If the sub-expression is NULL then the operator results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PatternMatch extends Operator {
	
	/**
	 * From IMS QTI:
	 * 
	 * The syntax for the regular expression language is as defined in 
	 * Appendix F of [XML_SCHEMA2].
	 * 
	 * @var string
	 */
	private $pattern;
	
	/**
	 * Create a new PatternMatch object.
	 * 
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param string $pattern A pattern to match or a variable reference.
	 * @throws InvalidArgumentException If $pattern is not a string value or if the $expressions count exceeds 1.
	 */
	public function __construct(ExpressionCollection $expressions, $pattern) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::SINGLE), array(OperatorBaseType::STRING));
		$this->setPattern($pattern);
	}
	
	/**
	 * Set the pattern to match.
	 * 
	 * @param string $pattern A pattern or a variable reference.
	 * @throws InvalidArgumentException If $pattern is not a string value.
	 */
	public function setPattern($pattern) {
		if (is_string($pattern)) {
			$this->pattern = $pattern;
		}
		else {
			$msg = "The pattern argument must be a string or a variable reference, '" . $pattern . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the pattern to match.
	 * 
	 * @return string A pattern or a variable reference.
	 */
	public function getPattern() {
		return $this->pattern;
	}
	
	public function getQTIClassName() {
		return 'patternMatch';
	}
}