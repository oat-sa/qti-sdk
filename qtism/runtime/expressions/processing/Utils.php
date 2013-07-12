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