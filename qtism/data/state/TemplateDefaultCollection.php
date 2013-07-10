<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of TemplateDefault objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TemplateDefaultCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of TemplateDefault.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of TemplateDefault.
	 */
	protected function checkType($value) {
		if (!$value instanceof TemplateDefault) {
			$msg = "TemplateDefaultCollection only accepts to store TemplateDefault objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}