<?php

namespace qtism\data\expressions\operators;

use qtism\data\expressions\ExpressionCollection;
use qtism\common\utils\Format;
use \InvalidArgumentException;

/**
 * The field-value operator takes a sub-expression with a record container value. The
 * result is the value of the field with the specified fieldIdentifier. If there is 
 * no field with that identifier then the result of the operator is NULL.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class FieldValue extends Operator {
	
	/**
	 * The identifier of the field to lookup.
	 * 
	 * @var string
	 */
	private $fieldIdentifier;
	
	/**
	 * Create a new instance of FieldValue.
	 * 
	 * @param ExpressionCollection $expressions A collection of Expression objects.
	 * @param string $fieldIdentifier A QTI Identifier.
	 */
	public function __construct(ExpressionCollection $expressions, $fieldIdentifier) {
		parent::__construct($expressions, 1, 1, array(OperatorCardinality::RECORD), array(OperatorBaseType::ANY));
		$this->setFieldIdentifier($fieldIdentifier);
	}
	
	/**
	 * Set the fieldIdentifier attribute.
	 * 
	 * @param string $fieldIdentifier A QTI Identifier.
	 * @throws InvalidArgumentException If $fieldIdentifier is not a valid QTI Identifier.
	 */
	public function setFieldIdentifier($fieldIdentifier) {
		if (Format::isIdentifier($fieldIdentifier)) {
			$this->fieldIdentifier = $fieldIdentifier;
		}
		else {
			$msg = "'${fieldIdentifier}' is not a valid QTI Identifier.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the fieldIdentifier attribute.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getFieldIdentifier() {
		return $this->fieldIdentifier;
	}
	
	public function getQtiClassName() {
		return 'fieldValue';
	}
}