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
use \LogicException;

/**
 * The AssessmentTestSession class represents a candidate session
 * for a given AssessmentTest.
 * 
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestSession extends State {
	
	/**
	 * A shortcut to assessmentItemRefs.
	 * 
	 * @var AssessmentItemRefCollection
	 */
	private $assessmentItemRefs;
	
	/**
	 * An array containing current assessmentItemSessions.
	 * 
	 * @var array
	 */
	private $assessmentItemSessions = array();
	
	/**
	 * Create a new AssessmentTestSession object.
	 *
	 * @param AssessmentTest $assessmentTest The AssessmentTest object which represents the assessmenTest the context belongs to.
	 * @throws InvalidArgumentException If an object of $array is not a Variable object.
	 */
	public function __construct(AssessmentTest $assessmentTest) {
		
		parent::__construct();
		
		$itemRefs = new AssessmentItemRefCollection($assessmentTest->getComponentsByClassName('assessmentItemRef')->getArrayCopy());
		$this->setAssessmentItemRefs($itemRefs);
		
		// Take the outcomeDeclaration objects of the global scope.
		foreach ($assessmentTest->getOutcomeDeclarations() as $globalOutcome) {
			$this->setVariable(OutcomeVariable::createFromDataModel($globalOutcome));
		}
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
	 * Get the array of running assessment item sessions.
	 * 
	 * @return array An array containing the running assessment item sessions.
	 */
	protected function &getAssessmentItemSessions() {
	    return $this->assessmentItemSessions;
	}
	
	/**
	 * Set the array of running assessment item sessions.
	 * 
	 * @param array $assessmentItemSession The array of running assessment item sessions.
	 */
	protected function setAssessmentItemSessions(array &$assessmentItemSessions) {
	    $this->assessmentItemSessions = $assessmentItemSessions;
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
	 * Add a variable (Variable object) to the current context. Variables that can be set using this method
	 * must have simple variable identifiers, in order to target the global AssessmentTestSession scope only.
	 * 
	 * @param Variable $variable A Variable object to add to the current context.
	 * @throws OutOfRangeException If the identifier of the given $variable is not a simple variable identifier (no prefix, no sequence number).
	 */
	public function setVariable(Variable $variable) {
	    
	    try {
	        $v = new VariableIdentifier($variable->getIdentifier());
	        
	        if ($v->hasPrefix() === true) {
	            $msg = "The variables set to the AssessmentTestSession global scope must have simple variable identifiers. ";
	            $msg.= "'" . $v->__toString() . "' given.";
	            throw new OutOfRangeException($msg);
	        }
	    }
	    catch (InvalidArgumentException $e) {
	        $variableIdentifier = $variable->getIdentifier();
	        $msg = "The identifier '${variableIdentifier}' of the variable to set is invalid.";
	        throw new OutOfRangeException($msg, 0, $e);
	    }
		
		$data = &$this->getDataPlaceHolder();
		$data[$v->__toString()] = $variable;
	}
	
	/**
	 * Get a variable from any scope of the AssessmentTestSession.
	 * 
	 * @return Variable A Variable object or null if no Variable object could be found for $variableIdentifier.
	 */
	public function getVariable($variableIdentifier) {
	    $v = new VariableIdentifier($variableIdentifier);
	    
	    if ($v->hasPrefix() === false) {
	        $data = &$this->getDataPlaceHolder();
	        if (isset($data[$v->getVariableName()])) {
	            return $data[$v->getVariableName()];
	        }
	    }
	    else {
	        // given with prefix.
	        $prefix = $v->getPrefix();
	        $itemSessions = &$this->getAssessmentItemSessions();
	        if (isset($itemSessions[$prefix])) {
	            
	            $sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
	            if (($itemSessions[$prefix][$sequence])) {
	               
	                $session = $itemSessions[$prefix][$sequence];
	                return $session->getVariable($v->getVariableName());
	            }
	        }
	    }
	    
	    return null;
	}
	
	/**
	 * Get a variable value from the current session using the bracket ([]) notation.
	 * 
	 * The value can be retrieved for any variables in the scope of the AssessmentTestSession. In other words,
	 * 
	 * * Outcome variables in the global scope of the AssessmentTestSession,
	 * * Outcome and Response variables in TestPart/AssessmentSection scopes.
	 * 
	 * @return mixed A QTI Runtime compliant value or NULL if no such value can be retrieved for $offset.
	 * @throws OutOfRangeException If $offset is not a string or $offset is not a valid variable identifier.
	 */
	public function offsetGet($offset) {
		
		try {
			$v = new VariableIdentifier($offset);
			
			if ($v->hasPrefix() === false) {
				// Simple variable name.
				// -> This means the requested variable is in the global test scope.
			    $data = &$this->getDataPlaceHolder();
			    
				$varName = $v->getVariableName();
				if (isset($data[$varName]) === false) {
					return null;
				}
				
				return $data[$offset]->getValue();
			}
			else {
				
				// prefix given.
				
				// - prefix targets an item?
			    $itemSessions = &$this->getAssessmentItemSessions();
			    $prefix = $v->getPrefix();
			    if (isset($itemSessions[$prefix])) {
			        $sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
			        	
			        if (isset($itemSessions[$prefix][$sequence])) {
			            $session = $itemSessions[$prefix][$sequence];
			            	
			            if (($var = $session->getVariable($v->getVariableName())) !== null) {
			                return $var->getValue();
			            }
			        }
			    }
			    
			    return null;
			}
		}
		catch (InvalidArgumentException $e) {
			$msg = "AssessmentTestSession object addressed with an invalid identifier '${offset}'.";
			throw new OutOfRangeException($msg, 0, $e);
		}
	}
	
	/**
	 * Set the value of a variable with identifier $offset.
	 * 
	 * @throws OutOfRangeException If $offset is not a string or an invalid variable identifier.
	 * @throws OutOfBoundsException If the variable with identifier $offset cannot be found.
	 */
	public function offsetSet($offset, $value) {
		
		if (gettype($offset) !== 'string') {
			$msg = "An AssessmentTestSession object must be addressed by string.";
			throw new OutOfRangeException($msg);
		}
		
		try {
			$v = new VariableIdentifier($offset);
			
			if ($v->hasPrefix() === false) {
				// global scope request.
				$data = &$this->getDataPlaceHolder();
				$varName = $v->getVariableName();
				if (isset($data[$varName]) === false) {
					$msg = "The variable '${varName}' to be set does not exist in the current context.";
					throw new OutOfBoundsException($msg);
				}
				
				$data[$offset]->setValue($value);
				return;
			}
			else {
				// prefix given.
				
				// - prefix targets an item ?
				$itemSessions = &$this->getAssessmentItemSessions();
				$prefix = $v->getPrefix();
				if (isset($itemSessions[$prefix])) {
					$sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
					
					if (isset($itemSessions[$prefix][$sequence])) {
					    $session = $itemSessions[$prefix][$sequence];
					    
					    if (($var = $session->getVariable($v->getVariableName())) !== null) {
					        $var->setValue($value);
					        return;
					    }
					}
				}

				$msg = "The variable '" . $v->__toString() . "' does not exist in the current context.";
				throw new OutOfBoundsException($msg);
			}
		}
		catch (InvalidArgumentException $e) {
			// Invalid variable identifier.
			$msg = "AssessmentTestSession object addressed with an invalid identifier '${offset}'.";
			throw new OutOfRangeException($msg, 0, $e);
		}
	}
	
	/**
	 * Unset a given variable's value identified by $offset from the global scope of the AssessmentTestSession.
	 * Please not that unsetting a variable's value keep the variable still instantiated
	 * in the context with its value replaced by NULL.
	 * 
	 * 
	 * @param string $offset A simple variable identifier (no prefix, no sequence number).
	 * @throws OutOfRangeException If $offset is not a simple variable identifier.
	 * @throws OutOfBoundsException If $offset does not refer to an existing variable in the global scope.
	 */
	public function offsetUnset($offset) {
		$data = &$this->getDataPlaceHolder();
		
		// Valid identifier?
		try {
			$v = new VariableIdentifier($offset);
			
			if ($v->hasPrefix() === true) {
			    $msg = "Only variables in the global scope of an AssessmentTestSession may be unset. '${offset}' is not in the global scope.";
			    throw new OutOfBoundsException($msg);
			}
			
			if (isset($data[$offset]) === true) {
			    $data[$offset]->setValue(null);
			}
			else {
			    $msg = "The variable '${offset}' does not exist in the AssessmentTestSession's global scope.";
			    throw new OutOfBoundsException($msg); 
			}
		}
		catch (InvalidArgumentException $e) {
			$msg = "The variable identifier '${offset}' is not a valid variable identifier.";
			throw new OutOfRangeException($msg, 0, $e);
		}
	}
	
	/**
	 * Check if a given variable identified by $offset exists in the global scope
	 * of the AssessmentTestSession.
	 * 
	 * @return boolean Whether the variable identified by $offset exists in the current context.
	 * @throws OutOfRangeException If $offset is not a simple variable identifier (no prefix, no sequence number).
	 */
	public function offsetExists($offset) {
	    try {
	        $v = new VariableIdentifier($offset);
	        
	        if ($v->hasPrefix() === true) {
	            $msg = "Test existence of a variable in an AssessmentTestSession may only be addressed with simple variable ";
	            $msg = "identifiers (no prefix, no sequence number). '" . $v->__toString() . "' given.";
	            throw new OutOfRangeException($msg, 0, $e);
	        }
	        
	        $data = &$this->getDataPlaceHolder();
	        return isset($data[$offset]);
	    }
	    catch (InvalidArgumentException $e) {
	       $msg = "'${offset}' is not a valid variable identifier.";
	       throw new OutOfRangeException($msg);
	    }
	}
	
	/**
	 * Begin an item session for the assessmentItemRef identified by the given $identifier.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws OutOfBoundsException If $identifier does not refer to any assessmentItemRef of the assessmentTest.
	 */
	protected function beginItemSession($identifier) {
	    $assessmentItemRefs = $this->getAssessmentItemRefs();
	    if (isset($assessmentItemRefs[$identifier]) === true) {
	        $itemSession = new AssessmentItemSession($assessmentItemRefs[$identifier]);
	        
	        $currentItemSessions = &$this->getAssessmentItemSessions();
	        if (isset($currentItemSessions[$identifier]) === false) {
	            // No item session registered for item $identifier.
	            $currentItemSessions[$identifier] = array();
	        }

	        $currentItemSessions[$identifier][] = $itemSession;
	    }
	    else {
	        $msg = "No assessmentItemRef with identifier '${identifier}' found in the current assessmentTest.";
	        throw new OutOfBoundsException($msg);
	    }
	}
	
	/**
	 * Add an item session to the current assessment test session.
	 * 
	 * @param AssessmentItemSession $session
	 * @param uinteger $sequenceNumber (optional) The sequence number of the item session. The sequence numbers in QTI begin at index 1. 
	 * @throws LogicException If the AssessmentItemRef object bound to $session is unknown by the AssessmentTestSession.
	 * @throws InvalidArgumentException If $sequenceNumber is not an integer >= 1.
	 */
	public function addItemSession(AssessmentItemSession $session, $sequenceNumber = 1) {
	    
	    if (gettype($sequenceNumber) !== 'integer' || $sequenceNumber < 1) {
	        $msg = "The sequenceNumber argument must be an integer value >= 1, '${sequenceNumber}' given.";
	        throw new InvalidArgumentException($msg); 
	    }
	    
	    $assessmentItemRefs = $this->getAssessmentItemRefs();
	    $sessionAssessmentItemRefIdentifier = $session->getAssessmentItemRef()->getIdentifier();
	    
	    if (isset($assessmentItemRefs[$sessionAssessmentItemRefIdentifier]) === false) {
	        // The session that is requested to be set is bound to an item
	        // which is not referenced in the test. This is a pure logic error.
	        $msg = "The item session to set is bound to an unknown AssessmentItemRef.";
	        throw new LogicException($msg);
	    }
	    
	    $currentItemSessions = &$this->getAssessmentItemSessions();
	    
	    // Something already registered?
	    if (isset($currentItemSessions[$sessionAssessmentItemRefIdentifier]) === false) {
	        $currentItemSessions[$sessionAssessmentItemRefIdentifier] = array();
	    }
	   
	    $currentItemSessions[$sessionAssessmentItemRefIdentifier][$sequenceNumber - 1] = $session; 
	}
	
	/**
	 * Get the AssessmentItemSession object related to $sessionIdentifier. If no AssessmentItemSession
	 * object with that given $sessionIdentifier is handled by the AssessmentTestSession, false is returned.
	 * 
	 * @param string $sessionIdentifier A valid variable identifier.
	 * @return AssessmentItemSession|false The related AssessmentItemSession object or false if not found.
	 * @throws OutOfRangeException If $sessionIdentifier is not a valid variable identifier.
	 */
	public function getItemSession($sessionIdentifier) {
	    
	    try {
	        $v = new VariableIdentifier($sessionIdentifier);
	        
	        $currentItemSessions = &$this->getAssessmentItemSessions();
	        
	        if ($v->hasPrefix() === true && isset($currentItemSessions[$v->getPrefix()]) === true) {
	            
	            $sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
	            $prefix = $v->getPrefix();
	            
	            if (isset($currentItemSessions[$prefix][$sequence]) === true) {
	                return $currentItemSessions[$prefix][$sequence];
	            }
	        }
	        
	        // No such item session found.
	        return false;
	    }
	    catch (InvalidArgumentException $e) {
	        $msg = "'" . $v->__toString() . "' is not a valid session identifier.";
	        throw new OutOfRangeException($msg);    
	    }
	}
}