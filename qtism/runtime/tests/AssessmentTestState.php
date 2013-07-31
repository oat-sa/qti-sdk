<?php

namespace qtism\runtime\tests;

use qtism\data\AssessmentItemRefCollection;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableIdentifier;
use qtism\runtime\common\Variable;
use \InvalidArgumentException;
use \OutOfRangeException;
use \OutOfBoundsException;

/**
 * The State of an AssessmentTest Instance.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestState extends State {
	
	/**
	 * The collection of AssessmentItemRef objects
	 * that are useful to the AssessmentTestState.
	 * 
	 * @var AssessmentItemRefCollection
	 */
	private $assessmentItemRefs;
	
	/**
	 * Create a new AssessmentTestState object.
	 *
	 * @param array $array An optional array of QTI Runtime Model Variable objects.
	 * @param AssessmentItemRefCollection A collection of QTI Data Model AssessmentItemRef objects. In other words, the execution context.
	 * @throws InvalidArgumentException If an object of $array is not a Variable object.
	 */
	public function __construct(array $array = array(), AssessmentItemRefCollection $assessmentItemRefs) {
		$this->setAssessmentItemRefs((empty($assessmentItemRefs)) ? new AssessmentItemRefCollection() : $assessmentItemRefs);
		parent::__construct($array);
	}
	
	/**
	 * Set the assessmentItemRefs bound to this AssessmentItemState.
	 * 
	 * @param AssessmentItemRefCollection $assessmentItemRefs
	 */
	public function setAssessmentItemRefs(AssessmentItemRefCollection $assessmentItemRefs = null) {
		$this->assessmentItemRefs = $assessmentItemRefs;
	}
	
	public function getAssessmentItemRefs() {
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
	
	public function setVariable(Variable $variable) {
		$v = new VariableIdentifier($variable->getIdentifier());
		
		if ($v->hasPrefix() === true) {
			// Check if the corresponding itemRef is registered.
			$items = $this->getAssessmentItemRefs();
			if (isset($items[$v->getPrefix()]) === false) {
				$prefix = $v->getPrefix();
				$msg = "No assessmentItemRef with identifier '${prefix}' found.";
				throw new InvalidArgumentException($msg);
			}	
		}
		
		$data = &$this->getDataPlaceHolder();
		$data[$v->getIdentifier()] = $variable;
	}
	
	public function offsetGet($offset) {
		
		if (gettype($offset) !== 'string') {
			$msg = "An AssessmentTestState object must be addressed by string.";
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
				
				return $data[$varName]->getValue();
			}
			else {
				// Prefixed variable Name.
				// -> The prefix is always an item identifier. Is it referenced ?
				$itemId = $v->getPrefix();
				$items = $this->getAssessmentItemRefs();
				if (isset($items[$itemId]) === false) {
					// The test does not contain the requested item.
					return null;
				}
				
				return $data[$v->getPrefix() . '.' . $v->getVariableName()]->getValue();
			}
		}
		catch (InvalidArgumentException $e) {
			$msg = "AssessmentTestState object addressed with an invalid identifier '${offset}'.";
			throw new OutOfRangeException($msg, 0, $e);
		}
	}
}