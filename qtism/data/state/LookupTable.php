<?php

namespace qtism\data\state;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;

/**
 * From IMS QTI:
 * 
 * An abstract class associated with an outcomeDeclaration used to create a lookup table 
 * from a numeric source value to a single outcome value in the declared value set. A 
 * lookup table works in the reverse sense to the similar mapping as it defines how a 
 * source numeric value is transformed into the outcome value, whereas a (response) mapping
 * defines how the response value is mapped onto a target numeric value.
 * 
 * The transformation takes place using the lookupOutcomeValue rule within responseProcessing 
 * or outcomeProcessing.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class LookupTable extends QtiComponent {
	
	/**
	 * The default outcome value to be used when no matching table entry is found. If omitted, 
	 * the NULL value is used. (QTI valueType attribute).
	 * 
	 * @var mixed
	 */
	private $defaultValue = null;
	
	/**
	 * Create a new instance of LookupTable.
	 * 
	 * @param mixed $defaultValue The default oucome value to be used when no matching table entry is found.
	 */
	public function __construct($defaultValue = null) {
		$this->setDefaultValue($defaultValue);
	}
	
	/**
	 * Get the default outcome value to be used when no matching table entry is found. If omitted,
	 * the NULL value is returned.
	 * 
	 * @return mixed A value.
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}
	
	/**
	 * Get the default outcome value to be used when no matching table entry is found.
	 * 
	 * @param mixed $defaultValue A value.
	 */
	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}
	
	public function getQTIClassName() {
		return 'lookupTable';
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}