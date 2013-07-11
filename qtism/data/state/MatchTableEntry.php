<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;
use qtism\data\state\Value;
use \InvalidArgumentException;

class MatchTableEntry extends QtiComponent {
	
	/**
	 * From IMS QTI:
	 * 
	 * The source integer that must be matched exactly.
	 * 
	 * @var int
	 */
	private $sourceValue;
	
	/**
	 * From IMS QTI:
	 * 
	 * The target value that is used to set the outcome when a match is found.
	 *
	 * 
	 * @var mixed
	 */
	private $targetValue;
	
	/**
	 * Create a new instance of MatchTableEntry.
	 * 
	 * @param int $sourceValue The source integer that must be matched exactly.
	 * @param mixed $targetValue The target value compliant with the baseType datatype.
	 */
	public function __construct($sourceValue, $targetValue) {
		$this->setSourceValue($sourceValue);
		$this->setTargetValue($targetValue);
	}
	
	/**
	 * Get the source integer that must be matched exactlty.
	 * 
	 * @return int An integer value.
	 */
	public function getSourceValue() {
		return $this->sourceValue;
	}
	
	/**
	 * Set the source integer that must be matched exactly.
	 * 
	 * @param integer $sourceValue An integer value.
	 * @throws InvalidArgumentException If $sourceValue is not an integer.
	 */
	public function setSourceValue($sourceValue) {
		if (is_int($sourceValue)) {
			$this->sourceValue = $sourceValue;
		}
		else {
			$msg = "SourceValue must be an integer, '" . gettype($sourceValue) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the target value.
	 * 
	 * @return mixed A value compliant with the QTI baseType datatype.
	 */
	public function getTargetValue() {
		return $this->targetValue;
	}
	
	/**
	 * Set the target value.
	 * 
	 * @param mixed $targetValue A Value object.
	 */
	public function setTargetValue($targetValue) {
		$this->targetValue = $targetValue;
	}
	
	public function getQtiClassName() {
		return 'matchTableEntry';
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}