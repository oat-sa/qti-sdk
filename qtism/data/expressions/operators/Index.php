<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The index operator takes a sub-expression with an ordered container value and any 
 * base-type. The result is the nth value of the container. The result has the same 
 * base-type as the sub-expression but single cardinality. The first value of a 
 * container has index 1, the second 2 and so on. n must be a positive integer.
 * If n exceeds the number of values in the container (or the sub-expression is NULL) 
 * then the result of the index operator is NULL. If n is an identifier, it is the 
 * value of n at runtime that is used.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Index extends Operator {
	
	/**
	 * The index to lookup.
	 * 
	 * @var integer|string
	 */
	private $n;
	
	/**
	 * Create a new Index.
	 * 
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param integer $n The index to lookup. It must be an integer or a variable reference.
	 * @throws InvalidArgumentException If $n is not an integer nor a variable reference.
	 */
	public function __construct(ExpressionCollection $expressions, $n) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::ORDERED), array(OperatorBaseType::ANY));
		$this->setN($n);
	}
	
	/**
	 * Set the n attribute.
	 * 
	 * @param integer|string $n The index to lookup. It must be an integer or a variable reference.
	 * @throws InvalidArgumentException If $n is not an integer nor a variable reference.
	 */
	public function setN($n) {
		if (is_int($n) || (is_string($n) && Format::isVariableRef($n))) {
			$this->n = $n;
		}
		else {
			$msg = "The n attribute must be an integer or a variable reference, '" . gettype($n) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the n attribute.
	 * 
	 * @return string|integer An integer or a variable reference.
	 */
	public function getN() {
		return $this->n;
	}
	
	public function getQtiClassName() {
		return 'index';
	}
}