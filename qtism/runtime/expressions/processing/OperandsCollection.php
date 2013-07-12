<?php

namespace qtism\runtime\expressions\processing;

use qtism\common\enums\BaseType;
use qtism\runtime\common\Container;
use qtism\common\collections\AbstractCollection;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\Utils as RuntimeUtils;
use InvalidArgumentException as InvalidArgumentException;

/**
 * A collection that aims at storing operands (QTI Runtime compliant values).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OperandsCollection extends AbstractCollection {

	/**
	 * Check if $value is a QTI Runtime compliant value.
	 * 
	 * @throws InvalidArgumentException If $value is not a QTI Runtime compliant value.
	 */
	protected function checkType($value) {
		if (RuntimeUtils::isRuntimeCompliant($value)) {
			return;
		}
		else if ($value instanceof MultipleContainer || $value instanceof OrderedContainer || $value instanceof RecordContainer) {
			return;
		}
		else {
			$value = (gettype($value) === 'object') ? get_class($value) : $value;
			$msg = "The OperandsCollection only accepts QTI Runtime compliant values, '" . $value . "' given.";
			throw new InvalidArgumentException($msg); 
		}
	}
	
	/**
	 * Wether the collection contains a QTI Runtime compliant value which is
	 * considered to be NULL.
	 * 
	 * @return boolean
	 */
	public function containsNull() {
		foreach ($this as $v) {
			if ($v instanceof Container && $v->isNull()) {
				return true;
			}
			else if ((is_string($v) && empty($v)) || is_null($v)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Whether the collection is exclusively composed of numeric values: primitive
	 * or Containers. Please note that:
	 * 
	 * * A primitive with the NULL value is not considered numeric.
	 * * Only float and integer primitive are considered numeric.
	 * * An empty Multiple/OrderedContainer with baseType integer or float is not considered numeric.
	 * * If the collection contains a container with cardinality RECORD, it is not considered exclusively numeric.
	 * * If the the current OperandsCollection is empty, false is returned.
	 * 
	 * @return boolean.
	 */
	public function exclusivelyNumeric() {
		
		if (count($this) === 0) {
			return false;
		}
		
		foreach ($this as $v) {
			if (($v instanceof MultipleContainer || $v instanceof OrderedContainer) && ($v->isNull() || ($v->getBaseType() !== BaseType::FLOAT && $v->getBaseType() !== BaseType::INTEGER))) {
				return false;
			}
			else if (!is_int($v) && !is_float($v) && !$v instanceof MultipleContainer && !$v instanceof OrderedContainer) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Wether the collection contains exclusively single cardinality values. If the container
	 * is empty or contains a null value, false is returned.
	 * 
	 * @return boolean
	 */
	public function exclusivelySingle() {
		
		if (count($this) === 0) {
			return false;
		}
		
		foreach ($this as $v) {
			if (is_null($v) || $v instanceof Container) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Whether the collection is exclusively composed of string values: primitive or Containers.
	 * Please note that:
	 * 
	 * * A primitive with the NULL value is not considered numeric.
	 * * An empty string is considered to be NULL and then not considered a valid string as per QTI 2.1 specification.
	 * * An empty Multiple/OrderedContainer with baseType string is not considered to contain strings.
	 * * If the collection contains a container with cardinality RECORD, it is not considered exclusively string.
	 * * If the the current OperandsCollection is empty, false is returned.
	 * 
	 * @return boolean
	 */
	public function exclusivelyString() {
		if (count($this) === 0) {
			return false;
		}
		
		foreach ($this as $v) {
			if (($v instanceof MultipleContainer || $v instanceof OrderedContainer) && ($v->isNull() || $v->getBaseType() !== BaseType::STRING)) {
				return false;
			}
			else if (!$v instanceof MultipleContainer && !$v instanceof OrderedContainer && (!is_string($v) || empty($v))) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Whether the collection contains only MultipleContainer OR OrderedContainer.
	 * 
	 * @return boolean
	 */
	public function exclusivelyMultipleOrOrdered() {
		
		if (count($this) === 0) {
			return false;
		}
		
		foreach ($this as $v) {
			if (!$v instanceof MultipleContainer && !$v instanceof OrderedContainer) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Whether the collection contains anything but a RecordContainer object.
	 * 
	 * @return boolean
	 */
	public function anythingButRecord() {
		
		foreach ($this as $v) {
			if ($v instanceof RecordContainer) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Whether the collection is composed of values with the same baseType.
	 * 
	 * * If any of the values has not the same baseType than other values in the collection, false is returned.
	 * * If the OperandsCollection is an empty collection, false is returned.
	 * * If the OperandsCollection contains a value considered to be null, false is returned.
	 * * If the OperandsCollection is composed exclusively by non-null RecordContainer objects, true is returned.
	 * 
	 * @return boolean
	 */
	public function sameBaseType() {
		$operandsCount = count($this);
		if ($operandsCount > 0 && !$this->containsNull()) {
			
			// take the first value of the collection as a referer.
			$refValue = $this[0];
			$refType = RuntimeUtils::inferBaseType($refValue);

			for ($i = 1; $i < $operandsCount; $i++) {
				$value = $this[$i];
				$testType = RuntimeUtils::inferBaseType($value);
				
				if ($testType !== $refType) {
					return false;
				}
			}
			
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Wether the collection is composed of values with the same cardinality. Please
	 * note that:
	 * 
	 * * If the OperandsCollection is empty, false is returned.
	 * * If the OperandsCollection contains a NULL value or a NULL container (empty), false is returned
	 * 
	 * @return boolean
	 */
	public function sameCardinality() {
		$operandsCount = count($this);
		if ($operandsCount > 0 && !$this->containsNull()) {
			$refType = RuntimeUtils::inferCardinality($this[0]);
			
			for ($i = 1; $i < $operandsCount; $i++) {
				if ($refType !== RuntimeUtils::inferCardinality($this[$i])) {
					return false;
				}
			}
			
			return true;
		}
		else {
			return false;
		}
	}
}