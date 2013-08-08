<?php

namespace qtism\runtime\tests;

use qtism\data\QtiIdentifiableCollection;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\OutcomeVariable;
use qtism\data\AssessmentTest;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableIdentifier;
use qtism\runtime\common\Variable;
use \InvalidArgumentException;
use \OutOfRangeException;
use \OutOfBoundsException;

/**
 * The Context of an AssessmentTest Instance.
 * 
 * AssessmentTextContext objects can run in strict mode. If this mode is enabled, OutOfBoundsException will be thrown
 * if requested (with the bracket ([]) notation variables do not exist in the context. Otherwise, when an unexistent 
 * variable is requested, the NULL value is returned.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestContext extends State {
	
	/**
	 * A shortcut to assessmentItemRefs.
	 * 
	 * @var AssessmentItemRefCollection
	 */
	private $assessmentItemRefs;
	
	/**
	 * Create a new AssessmentTestContext object.
	 *
	 * @param AssessmentTest $assessmentTest The AssessmentTest object which represents the assessmenTest the context belongs to.
	 * @throws InvalidArgumentException If an object of $array is not a Variable object.
	 */
	public function __construct(AssessmentTest $assessmentTest) {
		
		parent::__construct();
		
		$itemRefs = new AssessmentItemRefCollection($assessmentTest->getComponentsByClassName('assessmentItemRef')->getArrayCopy());
		$this->setAssessmentItemRefs($itemRefs);
		
		// Take the outcomeDeclaration objects of the global scope.
		foreach ($assessmentTest->getComponentsByClassName('outcomeDeclaration', false) as $globalOutcome) {
			$this->setVariable(OutcomeVariable::createFromDataModel($globalOutcome));
		}
		
		// Take the outcomeDeclaration/responseDeclaration objects from the
		// the item scopes.
		foreach ($this->getAssessmentItemRefs() as $itemRef) {
			
			foreach ($itemRef->getOutcomeDeclarations() as $outcome) {
				$var = OutcomeVariable::createFromDataModel($outcome);
				$unprefixedIdentifier = $var->getIdentifier();
				$var->setIdentifier($itemRef->getIdentifier() . '.' . $unprefixedIdentifier);
				$this->setVariable($var);
			}
			
			foreach ($itemRef->getResponseDeclarations() as $resp) {
				$var = ResponseVariable::createFromDataModel($resp);
				$unprefixedIdentifier = $var->getIdentifier();
				$var->setIdentifier($itemRef->getIdentifier() . '.' . $unprefixedIdentifier);
				$this->setVariable($var);
			}
		}
	}
	
	/**
	 * Returns the test-level outcome variables.
	 */
	public function getTestLevelOutcomeVariables() {
		// @todo implement AssessmentTestContext::getTestLevelOutcomeVariables.
	}
	
	/**
	 * Returns the item-level variables (both outcome and response variables).
	 */
	public function getItemLevelVariables() {
		// @todo implement AssessmentTestContext::getItemLevelVariables.
	}
	
	/**
	 * Set the assessmentItemRef objects involved in the context.
	 * 
	 * @param AssessmentItemRefCollection $assessmentItemRefs A Collection of AssessmentItemRef objects.
	 */
	protected function setAssessmentItemRefs(AssessmentItemRefCollection $assessmentItemRefs) {
		$this->assessmentItemRefs = $assessmentItemRefs;
	}
	
	/**
	 * Get the assessmentItemRef objects involved in the context.
	 * 
	 * @return AssessmentItemRefCollection A Collection of AssessmentItemRef objects.
	 */
	protected function getAssessmentItemRefs() {
		return $this->assessmentItemRefs;
	}
	
	/**
	 * Get a weight by using a prefixed identifier e.g. 'Q01.weight1'
	 * where 'Q01' is the item the requested weight belongs to, and 'weight1' is the
	 * actual identifier of the weight.
	 * 
	 * @param string|VariableIdentifier $identifier A prefixed string identifier or a PrefixedVariableName object.
	 * @return false|float The weight corresponding to $identifier or false if such a weight do not exist.
	 * @throws InvalidArgumentException If $identifier is malformed string, not a VariableIdentifier object, or if the VariableIdentifier object has no prefix.
	 */
	public function getWeight($identifier) {
		if (gettype($identifier) === 'string') {
			try {
				$identifier = new VariableIdentifier($identifier);
			}
			catch (InvalidArgumentException $e) {
				$msg = "'${identifier}' is not a valid variable identifier.";
				throw new InvalidArgumentException($msg, 0, $e);
			}
		}
		else if (!$identifier instanceof VariableIdentifier) {
			$msg = "The given identifier argument is not a string, nor a VariableIdentifier object.";
			throw new InvalidArgumentException($msg);
		}
		
		if ($identifier->hasPrefix() === false) {
			$msg = "The given variable identifier '" . $identifier->getIdentifier() . "' has no prefix.";
			throw new InvalidArgumentException($msg);
		}
		
		// get the item the weight should belong to.
		$assessmentItemRefs = $this->getAssessmentItemRefs();
		$expectedItemId = $identifier->getPrefix();
		if (isset($assessmentItemRefs[$expectedItemId])) {
			$weights = $assessmentItemRefs[$expectedItemId]->getWeights();
			
			if (isset($weights[$identifier->getVariableName()])) {
				return $weights[$identifier->getVariableName()];
			}
		}
		
		return false;
	}
	
	/**
	 * Add a variable (Variable object) to the current context.
	 * 
	 * @param Variable $variable A Variable object to add to the current context.
	 * @throws InvalidArgumentException If the identifier of the variable is prefixed by an item identifier, but this is item is not referenced is not referenced in the current context.
	 */
	public function setVariable(Variable $variable) {
		$v = new VariableIdentifier($variable->getIdentifier());
		$data = &$this->getDataPlaceHolder();
		$data[$v->__toString()] = $variable;
	}
	
	/**
	 * Get a variable value from the current context using the bracket ([]) notation.
	 * 
	 * @return mixed A QTI Runtime compliant value or NULL if no such value can be retrieved for $offset.
	 * @throws OutOfRangeException If $offset is not a string or $offset is not a valid variable identifier.
	 * @throws OutOfBoundsException If strict mode enabled only. If a variable cannot be found.
	 */
	public function offsetGet($offset) {
		
		if (gettype($offset) !== 'string') {
			$msg = "An AssessmentTestContext object must be addressed by string.";
			throw new OutOfRangeException($msg);
		}
		
		try {
			$v = new VariableIdentifier($offset);
			$data = &$this->getDataPlaceHolder();
			
			if ($v->hasPrefix() === false) {
				// Simple variable name.
				// -> This means the requested variable is in the global test scope.
				$varName = $v->getVariableName();
				if (isset($data[$varName]) === false) {
					return null;
				}
				
				return $data[$offset]->getValue();
			}
			else {
				
				if (isset($data[$offset])) {
					return $data[$offset]->getValue();
				}
				else {
					return null;
				}
			}
		}
		catch (InvalidArgumentException $e) {
			$msg = "AssessmentTestContext object addressed with an invalid identifier '${offset}'.";
			throw new OutOfRangeException($msg, 0, $e);
		}
	}
	
	/**
	 * Set the value of a variable with identifier $offset. Offset cannot contain a sequence number. Indeed,
	 * the AssessmentTestContext object takes care itself of the sequencing of the values. It cannot be done manually.
	 * 
	 * @throws OutOfRangeException If $offset is not a string or an invalid variable identifier.
	 * @throws OutOfBoundsException If a variable cannot be found or if trying to set a variable's value with a sequence number.
	 */
	public function offsetSet($offset, $value) {
		
		if (gettype($offset) !== 'string') {
			$msg = "An AssessmentTestContext object must be addressed by string.";
			throw new OutOfRangeException($msg);
		}
		
		try {
			$v = new VariableIdentifier($offset);
			$data = &$this->getDataPlaceHolder();
			
			if ($v->hasPrefix() === false) {
				// global scope request.
				$varName = $v->getVariableName();
				if (isset($data[$varName]) === false) {
					$msg = "The variable '${varName}' to be set does not exist in the current context.";
					throw new OutOfBoundsException($msg);
				}
				
				$data[$offset]->setValue($value);
			}
			else if ($v->hasSequenceNumber() === false) {
				// prefix given, no sequence number
				if (isset($data[$offset])) {
					$data[$offset]->setValue($value);
				}
				else {
					$msg = "No variable '" . $v->getVariableName() . "' found for item '" . $v->getPrefix() . "'.";
					throw new OutOfBoundsException($msg);
				}
			}
			else {
				// prefix and sequence number given.
				$msg = "The variable '${offset}' cannot be set using a sequence number.";
				throw new OutOfBoundsException($msg);
			}
		}
		catch (InvalidArgumentException $e) {
			// Invalid variable identifier.
			$msg = "AssessmentTestContext object addressed with an invalid identifier '${offset}'.";
			throw new OutOfRangeException($msg, 0, $e);
		}
	}
	
	/**
	 * Unset a given variable's value identified by $offset from the current context.
	 * Please not that unsetting a variable's value keep the variable still instantiated
	 * in the context with the previous value replaced by NULL.
	 * 
	 * If strict mode is enabled, an OutOfBoundsException will be thrown if:
	 * 
	 * * The $offset contains a sequence number.
	 * * The $offset refers to an unexistent variable.
	 * 
	 * @param string $offset
	 * @throws OutOfRangeException
	 * @throws OutOfBoundsException
	 */
	public function offsetUnset($offset) {
		$data = &$this->getDataPlaceHolder();
		
		// Valid identifier?
		try {
			$v = new VariableIdentifier($offset);
		}
		catch (InvalidArgumentException $e) {
			$msg = "The variable identifier '${offset}' is invalid.";
			throw new OutOfRangeException($msg, 0, $e);
		}
		
		if ($v->hasSequenceNumber() === true) {
			$msg = "Variables contained in AssessmentTestContext objects cannot be unset with a sequence number.";
			throw new OutOfBoundsException($msg);
		}
		
		// No strict mode.
		if (isset($data[$offset]) === true) {
			$data[$offset]->setValue(null);
		}
	}
	
	/**
	 * Check if a given variable identified by $offset exists in the 
	 * current context.
	 * 
	 * This method throws an OutOfRangeException in strict mode only, 
	 * if the identifier $offset is a invalid identifier.
	 * 
	 * This method throws an OutOfBoundsException in strict mode only,
	 * if the identifier $offset does not match any variable in the current state.
	 * 
	 * @throws OutOfRangeException In strict mode only, if the given $offset is not a valid variable identifier.
	 * @return boolean Whether the variable identified by $offset exists in the current context.
	 */
	public function offsetExists($offset) {
		$data = &$this->getDataPlaceHolder();
		return isset($data[$offset]);
	}
}