<?php

namespace qtism\runtime\tests;

use qtism\data\AssessmentItemRefCollection;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableIdentifier;
use \InvalidArgumentException;

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
	public function __construct(array $array = array(), AssessmentItemRefCollection $assessmentItemRefs = null) {
		parent::__construct($array);
		$this->setAssessmentItemRefs((empty($assessmentItemRefs)) ? new AssessmentItemRefCollection() : $assessmentItemRefs);
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
}