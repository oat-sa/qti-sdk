<?php

namespace qtism\data\expressions\operators;

use qtism\common\enums\Cardinality;
use qtism\data\expressions\ExpressionCollection;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * The statsOperator operator takes 1 sub-expression which is a container of multiple 
 * or ordered cardinality and has a numerical base-type. The result is a single float.
 * If the sub-expression or any value contained therein is NULL, the result is NULL.
 * If any value contained in the sub-expression is not a numerical value, then the 
 * result is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StatsOperator extends Operator {
	
	/**
	 * The name of the statistics operator to use.
	 * 
	 * @var integer
	 */
	private $name;
	
	/**
	 * Create a new instance of StatsOperator.
	 * 
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param integer $name A value from the Statistics enumeration.
	 * @throws InvalidArgumentException If $name is not a value from the Statistics enumeration or if the count of $expressions is greather than 1.
	 */
	public function __construct(ExpressionCollection $expressions, $name) {
		parent::__construct($expressions, 1, 1, array(Cardinality::MULTIPLE, Cardinality::ORDERED), array(OperatorBaseType::INTEGER, OperatorBaseType::FLOAT));

		$this->setName($name);
	}
	
	/**
	 * Set the statistics operator to use.
	 * 
	 * @param integer $name A value from the Statistics enumeration.
	 * @throws InvalidArgumentException If $name is not a value from the Statistics enumeration.
	 */
	public function setName($name) {
		if (in_array($name, Statistics::asArray())) {
			$this->name = $name;
		}
		else {
			$msg = "The name argument must be a value from the Statistics enumeration, '" . $name . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the name of the statistics operator to use.
	 * 
	 * @return integer A value from the Statistics enumeration.
	 */
	public function getName() {
		return $this->name;
	}
	
	public function getQtiClassName() {
		return 'statsOperator';
	}
}