<?php

namespace qtism\runtime\expressions\processing;

use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use \InvalidArgumentException;

/**
 * Utility class for Processors.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Utils {
	
	/**
	 * Check if a value being used by a Processor is numeric in QTI Runtime context.
	 * 
	 * @param mixed $value
	 * @return boolean Whether $value is numeric.
	 */
	public static function isNumeric($value) {
		if (is_numeric($value)) {
			return true;
		}
		else if ($value instanceof MultipleContainer && ($value->getBaseType() === BaseType::INTEGER || $value->getBaseType() === BaseType::FLOAT)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if a value being used by a Processor has a single cardinality in QTI Runtime Context.
	 * 
	 * @param mixed $value
	 * @return boolean Whether $value is considered to have a single cardinality.
	 */
	public static function isSingle($value) {
		return gettype($value) !== 'object';
	}
	
	/**
	 * Check if a value being used by a Processor has a multiple cardinality in QTI Runtime Context.
	 * 
	 * Take care, this method will return true if $value is a MultipleContainer object but also if it
	 * an OrderedContainer object.
	 * 
	 * @param mixed $value
	 * @return boolean Whether $value is considered to have a multiple cardinality.
	 */
	public static function isMultiple($value) {
		return gettype($value) === 'object' && ($value instanceof MultipleContainer || $value instanceof OrderedContainer);
	}
	
	/**
	 * Check if a value being used by a Processor as ordered cardinality in QTI Runtime Context.
	 * 
	 * @param mixed $value
	 * @return boolean Whether $value is considered to have an ordered cardinality.
	 */
	public static function isOrdered($value) {
		return gettype($value) === 'object' && $value instanceof OrderedContainer;
	}
	
	/**
	 * Removes trailing and ending braces ('{' and '}') from a variableRef.
	 * 
	 * @return string A sanitized variableRef.
	 */
	public static function sanitizeVariableRef($variableRef) {
		if (is_string($variableRef)) {
			return trim($variableRef, '{}');
		}
		else {
			$msg = "The Utils::sanitizeVariableRef method only accepts a string argument, '" . gettype($variableRef) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}