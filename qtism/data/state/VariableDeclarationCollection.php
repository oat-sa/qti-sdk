<?php

namespace qtism\data\state;

use qtism\data\QtiIdentifiableCollection;
use \InvalidArgumentException as InvalidArgumentException;

/**
 * A collection of VariableDeclaration objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class VariableDeclarationCollection extends QtiIdentifiableCollection {
	
	/**
	 * Check if a given $value is an instance of VariableDeclaration.
	 * 
	 * @throws InvalidArgumentException If the given $value is not an instance of VariableDeclaration.
	 */
	protected function checkType($value) {
		if (!$value instanceof VariableDeclaration) {
			$msg = "InterpolationTableEntryCollection only accepts to store VariableDeclaration objects, '" . gettype($value) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
}