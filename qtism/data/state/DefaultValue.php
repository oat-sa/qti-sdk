<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use \InvalidArgumentException;
use qtism\data\QtiComponent;

/**
 * The DefaultValue class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DefaultValue extends QtiComponent {
	
	/**
	 * From IMS QTI:
	 * 
	 * A human readable interpretation of the default value.
	 * 
	 * @var string
	 */
	private $interpretation = '';
	
	/**
	 * From IMS QTI:
	 * 
	 * The order of the values is significant only if the variable being set 
	 * has ordered cardinality.
	 * 
	 * @var ValueCollection
	 */
	private $values;
	
	/**
	 * Create a new instance of DefaultValue.
	 * 
	 * @param ValueCollection $values A collection of Value objects with at least one Value object.
	 * @param string $interpretation A human-readable interpretation of the DefaultValue.
	 * @throws InvalidArgumentException If $values does not contain at least one Value object or $interpretation is not a string.
	 */
	public function __construct(ValueCollection $values, $interpretation = '') {
		$this->setValues($values);
		$this->setInterpretation($interpretation);
	}
	
	/**
	 * Get a human-readable interpretation of the value. Returns an empty string
	 * if not specified.
	 * 
	 * @return string An interpretation.
	 */
	public function getInterpretation() {
		return $this->interpretation;
	}
	
	/**
	 * Set a human-readable interpretation of the value. Set an empty string
	 * if not specified.
	 * 
	 * @param string $interpretation An interpretation.
	 * @throws InvalidArgumentException If $interpretation is not a string.
	 */
	public function setInterpretation($interpretation) {
		if (is_string($interpretation)) {
			$this->interpretation = $interpretation;
		}
		else {
			$msg = "Interpretation must be a string, '" . gettype($interpretation) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the intrinsic values of the DefaultValue.
	 * 
	 * @return ValueCollection A ValueCollection containing at least one Value object.
	 */
	public function getValues() {
		return $this->values;
	}
	
	/**
	 * Set the intrinsic values of the DefaultValue.
	 * 
	 * @param ValueCollection $values A collection of Value objects containing at least one Value object.
	 * @throws InvalidArgumentException If $values does not contain at least one Value object.
	 */
	public function setValues(ValueCollection $values) {
		if (count($values) > 0) {
			$this->values = $values;
		}
		else {
			$msg = "Values must contain at lease one Value.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQTIClassName() {
		return 'defaultValue';
	}
	
	public function getComponents() {
		return new QtiComponentCollection($this->getValues()->getArrayCopy());
	}
}