<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The stringMatch operator takes two sub-expressions which must have single and a 
 * base-type of string. The result is a single boolean with a value of true if the 
 * two strings match according to the comparison rules defined by the attributes 
 * below and false if they don't. If either sub-expression is NULL then the operator 
 * results in NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StringMatch extends Operator {
	
	/**
	 * From IMS QTI:
	 * 
	 * Whether or not the match is to be carried out case sensitively.
	 * 
	 * @var boolean
	 */
	private $caseSensitive;
	
	/**
	 * From IMS QTI:
	 * 
	 * This attribute is now deprecated, the substring operator should be used instead.
	 * If true, then the comparison returns true if the first string contains the 
	 * second one, otherwise it returns true only if they match entirely.
	 * 
	 * @var boolean
	 */
	private $substring = false;
	
	/**
	 * Create a new instance of StringMatch.
	 * 
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param boolean $caseSensitive Whether or not the match to be carried out case sensitively.
	 * @param boolean $substring Deprecated argument, use the substring operator instead.
	 * @throws InvalidArgumentException If $caseSensitive or $substring are not booleans or if the $expressions count is greather than 2.
	 */
	public function __construct(ExpressionCollection $expressions, $caseSensitive, $substring = false) {
		parent::__construct($expressions, 2, 2, array(OperatorCardinality::SINGLE), array(OperatorBaseType::STRING));
		$this->setCaseSensitive($caseSensitive);
		$this->setSubstring($substring);
	}
	
	/**
	 * Set Wheter or not the match is to be carried out case sensitively.
	 * 
	 * @param boolean $caseSensitive Case sensitiveness.
	 * @throws InvalidArgumentException If $caseSensitive is not a boolean.
	 */
	public function setCaseSensitive($caseSensitive) {
		if (is_bool($caseSensitive)) {
			$this->caseSensitive = $caseSensitive;
		}
		else {
			$msg = "The caseSensitive argument must be a boolean, '" . gettype($caseSensitive) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Wether or not the match is to be carried out case sensitively.
	 * 
	 * @return boolean True if it has to, false otherwise.
	 */
	public function isCaseSensitive() {
		return $this->caseSensitive;
	}
	
	/**
	 * Set the substring attribute.
	 * 
	 * @param boolean $substring A boolean value.
	 * @throws InvalidArgumentException If $substring is not a boolean.
	 * @deprecated
	 */
	public function setSubstring($substring) {
		if (is_bool($substring)) {
			$this->substring = $substring;
		}
		else {
			$msg = "The substring argument must be a boolean, '" . gettype($substring) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the substring attribute.
	 * 
	 * @return boolean
	 * @deprecated
	 */
	public function mustSubstring() {
		return $this->substring;
	}
	
	public function getQtiClassName() {
		return 'stringMatch';
	}
}