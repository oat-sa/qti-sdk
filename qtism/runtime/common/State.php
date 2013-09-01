<?php

namespace qtism\runtime\common;

use qtism\data\VariableCollection;

use qtism\common\collections\AbstractCollection;
use \OutOfRangeException;
use \OutOfBoundsException;
use \InvalidArgumentException;

/**
 * The State class represents a state composed by a set of Variable objects. 
 * 
 * This class implements Countable, Iterator and ArrayAccess thanks to its inheritance from the 
 * qtism\common\collections\AbstractCollection. 
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @see qtism\runtime\common\Variable For a description of the Variable class.
 * @see \OutOfRangeException For a description of the SPL OutOfRangeException class.
 * @see \OutOfBoundsException For a description of the SPL OutOfRangeException class.
 * @see \InvalidArgumentException For a description of the SPL InvalidArgumentException class.
 */
class State extends AbstractCollection {
	
	/**
	 * Create a new State object.
	 * 
	 * @param array $array An optional array of Variable objects.
	 * @throws InvalidArgumentException If an object of $array is not a Variable object.
	 */
	public function __construct(array $array = array()) {
		parent::__construct();
		foreach ($array as $a) {
			if ($a instanceof Variable) {
				$this->setVariable($a);
			}
			else {
				$msg = "An object given to State::__construct is not a Variable object.";
				throw new InvalidArgumentException($msg);
			}
		}
	}
	
	public function setVariable(Variable $variable) {
		$data = &$this->getDataPlaceHolder();
		$data[$variable->getIdentifier()] = $variable;
	}
	
	/**
	 * Get a variable with the identifier $variableIdentifier.
	 * 
	 * @param string $variableIdentifier A QTI identifier.
	 * @return Variable A Variable object or null if the $variableIdentifier does not match any Variable object stored in the State.
	 */
	public function getVariable($variableIdentifier) {
		$data = &$this->getDataPlaceHolder();
		if (isset($data[$variableIdentifier])) {
			return $data[$variableIdentifier];
		}
		else {
			return null;
		}
	}
	
	/**
	 * Unset a variable from the current state. In other words
	 * the relevant Variable object is removed from the state. 
	 * 
	 * @param string|Variable $variable The identifier of the variable or a Variable object to unset.
	 * @throws InvalidArgumentException If $variable is not a string nor a Variable object.
	 * @throws OutOfBoundsException If no variable in the current state matches $variable.
	 */
	public function unsetVariable($variable) {
		$data = &$this->getDataPlaceHolder();
		
		if (is_string($variable)) {
			$variableIdentifier = $variable;
		}
		else if ($variable instanceof Variable) {
			$variableIdentifier = $variable->getIdentifier();
		}
		else {
			$msg = "The variable argument must be a Variable object or a string, '" . $variable . "' given";
			throw new InvalidArgumentException($msg);
		}
		
		if (isset($data[$variableIdentifier])) {
			unset($data[$variable->getIdentifier()]);
		}
		else {
			$msg = "No Variable object with identifier '${variableIdentifier}' found in the current State object.";
			throw new OutOfBoundsException($msg);
		}
	}
	
	public function offsetSet($offset, $value) {
		if (is_string($offset) && !empty($offset)) {
			$placeholder = &$this->getDataPlaceHolder();
			
			if (isset($placeholder[$offset])) {
				$placeholder[$offset]->setValue($value);
			}
			else {
				$msg = "No Variable object with identifier '${offset}' found in the current State object.";
				throw new OutOfBoundsException($msg);
			}
		}
		else {
			$msg = "A State object can only be adressed by a valid string.";
			throw new OutOfRangeException($msg);
		}
	}
	
	public function offsetGet($offset) {
		if (is_string($offset) && !empty($offset)) {
			$data = &$this->getDataPlaceHolder();
			if (isset($data[$offset])) {
				return $data[$offset]->getValue();
			}
			else {
				return null;
			}
		}
		else {
			$msg = "A State object can only be addressed by a valid string.";
			throw new OutOfRangeException($msg);
		}
	}
	
	/**
	 * Reset all test-level outcome variables to their defaults.
	 * 
	 */
	public function resetOutcomeVariables() {
	    $data = &$this->getDataPlaceHolder();
	    
	    foreach (array_keys($data) as $k) {
	        if ($data[$k] instanceof OutcomeVariable) {
	            $data[$k]->applyDefaultValue();
	        }
	    }
	}
	
	public function checkType($value) {
		if (!$value instanceof Variable) {
			$msg = "A State object stores Variable objects only.";
			throw new InvalidArgumentException($msg);
		}
	}
}