<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of OutcomeDeclaration objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeDeclarationCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of OutcomeDeclaration.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of OutcomeDeclaration.
	 */
	protected function checkType($value) {
		if (!$value instanceof OutcomeDeclaration) {
			$msg = "OutcomeDeclarationCollection only accepts to store OutcomeDeclaration objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}