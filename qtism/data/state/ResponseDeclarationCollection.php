<?php

namespace qtism\data\state;

use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of ResponseDeclaration objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseDeclarationCollection extends AbstractCollection {
	
	/**
	 * Check if a given $value is an instance of ResponseDeclaration.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of ResponseDeclaration.
	 */
	protected function checkType($value) {
		if (!$value instanceof ResponseDeclaration) {
			$msg = "ResponseDeclarationCollection only accepts to store ResponseDeclaration objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}