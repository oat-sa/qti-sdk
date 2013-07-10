<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use \SplObserver;
use \SplSubject;
use \SplObjectStorage;
use \InvalidArgumentException as InvalidArgumentException;
use qtism\common\utils\Format as Format;

/**
 * From IMS QTI:
 * 
 * The contribution of an individual item score to an overall test score typically 
 * varies from test to test. The score of the item is said to be weighted. Weights 
 * are defined as part of each reference to an item (assessmentItemRef) within a test.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Weight extends QtiComponent {
	
	/**
	 * A QTI identifier.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * A floating point value corresponding to the wheight to be applied on outcome
	 * variables.
	 * 
	 * @var int|float
	 */
	private $value;
	
	/**
	 * Create a new instance of Weight.
	 * 
	 * @param string $identifier A QTI identifier.
	 * @param int|float $value An integer/float value.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI identifier or if $value is not a float nor an integer.
	 */
	public function __construct($identifier, $value) {
		$this->setIdentifier($identifier);
		$this->setValue($value);
	}
	
	/**
	 * Get the identifier of the Weight.
	 * 
	 * @return string A QTI identifier.
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * Set the identifier of the Weight.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI identifier.
	 */
	public function setIdentifier($identifier) {
		if (Format::isIdentifier($identifier)) {
			$this->identifier = $identifier;
		}
		else {
			$msg = "'${identifier}' is not a valid QTI Identifier.";
			throw new InvalidArgumentException();
		}
	}
	
	/**
	 * Get the value of the Weight.
	 * 
	 * @return int|float An integer/float value.
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * Set the value of the Weight.
	 * 
	 * @param int|float $value A in integer/float value.
	 * @throws InvalidArgumentException If $value is not an integer nor a float.
	 */
	public function setValue($value) {
		if (is_int($value) || is_float($value)) {
			$this->value = $value;
		}
		else {
			$msg = "The value of a Weight must be a valid integer or float value, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQTIClassName() {
		return 'weight';
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}