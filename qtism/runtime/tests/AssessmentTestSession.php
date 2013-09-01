<?php

namespace qtism\runtime\tests;

use qtism\runtime\common\ProcessingException;
use qtism\runtime\processing\OutcomeProcessingEngine;
use qtism\common\collections\IdentifierCollection;
use qtism\data\NavigationMode;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\Route;
use qtism\runtime\tests\RouteItem;
use qtism\runtime\common\OutcomeVariable;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableIdentifier;
use qtism\runtime\common\Variable;
use \SplObjectStorage;
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
     * The AssessmentItemSession store.
     * 
     * @var AssessmentItemSessionStore
     */
	private $assessmentItemSessionStore;
	
	/**
	 * The route to be taken by this AssessmentTestSession.
	 * 
	 * @var Route
	 */
	private $route;
	
	/**
	 * The state of the AssessmentTestSession.
	 * 
	 * @var integer
	 */
	private $state;
	
	/**
	 * The AssessmentTest the AssessmentTestSession is an instance of.
	 * 
	 * @var AssessmentTest
	 */
	private $assessmentTest;
	
	/**
	 * A map (indexed by AssessmentItemRef objects) to store
	 * the last occurence that has one of its variable updated.
	 * 
	 * @var SplObjectStorage
	 */
	private $lastOccurenceUpdate;
	
	/**
	 * Create a new AssessmentTestSession object.
	 *
	 * @param AssessmentTest $assessmentTest The AssessmentTest object which represents the assessmenTest the context belongs to.
	 * @param Route $route The sequence of items that has to be taken for the session.
	 */
	public function __construct(AssessmentTest $assessmentTest, Route $route) {
		
		parent::__construct();
		$this->setAssessmentTest($assessmentTest);
		$this->setRoute($route);
		$this->setAssessmentItemSessionStore(new AssessmentItemSessionStore());
		$this->setLastOccurenceUpdate(new SplObjectStorage());
		
		// Take the outcomeDeclaration objects of the global scope.
		// Instantiate them with their defaults.
		foreach ($this->getAssessmentTest()->getOutcomeDeclarations() as $globalOutcome) {
		    $variable = OutcomeVariable::createFromDataModel($globalOutcome);
			$variable->applyDefaultValue();
		    $this->setVariable($variable);
		}
		
		$this->setState(AssessmentTestSessionState::INITIAL);
	}
	
	/**
	 * Get the AssessmentTest object the AssessmentTestSession is an instance of.
	 * 
	 * @return AssessmentTest An AssessmentTest object.
	 */
	public function getAssessmentTest() {
	    return $this->assessmentTest;
	}
	
	/**
	 * Set the AssessmentTest object the AssessmentTestSession is an instance of.
	 * 
	 * @param AssessmentTest $assessmentTest
	 */
	protected function setAssessmentTest(AssessmentTest $assessmentTest) {
	    $this->assessmentTest = $assessmentTest;
	}
	
	/**
	 * Get the assessmentItemRef objects involved in the context.
	 * 
	 * @return AssessmentItemRefCollection A Collection of AssessmentItemRef objects.
	 */
	protected function getAssessmentItemRefs() {
		return $this->getRoute()->getAssessmentItemRefs();
	}
	
	/**
	 * Get the Route object describing the succession of items to be possibly taken.
	 * 
	 * @return Route A Route object.
	 */
	public function getRoute() {
	    return $this->route;
	}
	
	/**
	 * Set the Route object describing the succession of items to be possibly taken.
	 * 
	 * @param Route $route A route object.
	 */
	public function setRoute(Route $route) {
	    $this->route = $route;
	}
	
	/**
	 * Get the current status of the AssessmentTestSession.
	 * 
	 * @return integer A value from the AssessmentTestSessionState enumeration.
	 */
	public function getState() {
	    return $this->state;
	}
	
	/**
	 * Set the current status of the AssessmentTestSession.
	 * 
	 * @param integer $state A value from the AssessmentTestSessionState enumeration.
	 */
	public function setState($state) {
	    if (in_array($state, AssessmentTestSessionState::asArray()) === true) {
	        $this->state = $state;
	    }
	    else {
	        $msg = "The state argument must be a value from the AssessmentTestSessionState enumeration";
	        throw new InvalidArgumentException($msg);
	    }
	}
	
	/**
	 * Get the AssessmentItemSessionStore.
	 * 
	 * @return AssessmentItemSessionStore
	 */
	protected function getAssessmentItemSessionStore() {
	    return $this->assessmentItemSessionStore;
	}
	
	/**
	 * Set the AssessmentItemSessionStore.
	 * 
	 * @param AssessmentItemSessionStore $assessmentItemSessionStore
	 */
	protected function setAssessmentItemSessionStore(AssessmentItemSessionStore $assessmentItemSessionStore) {
	    $this->assessmentItemSessionStore = $assessmentItemSessionStore;
	}
	
	/**
	 * Get a weight by using a prefixed identifier e.g. 'Q01.weight1'
	 * where 'Q01' is the item the requested weight belongs to, and 'weight1' is the
	 * actual identifier of the weight.
	 * 
	 * @param string|VariableIdentifier $identifier A prefixed string identifier or a PrefixedVariableName object.
	 * @return false|Weight The weight corresponding to $identifier or false if such a weight do not exist.
	 * @throws InvalidArgumentException If $identifier is malformed string, not a VariableIdentifier object, or if the VariableIdentifier object has no prefix.
	 */
	public function getWeight($identifier) {
		if (gettype($identifier) === 'string') {
			try {
				$identifier = new VariableIdentifier($identifier);
				if ($identifier->hasSequenceNumber() === true) {
				    $msg = "The identifier ('${identifier}') cannot contain a sequence number.";
				    throw new InvalidArgumentException($msg);
				}
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
		
		// identifier with prefix or not, no sequence number.
		if ($identifier->hasPrefix() === false) {
			$itemRefs = $this->getAssessmentItemRefs();
		    foreach ($itemRefs->getKeys() as $itemKey) {
		        $itemRef = $itemRefs[$itemKey];
		        $weights = $itemRef->getWeights();
		        
		        foreach ($weights->getKeys() as $weightKey) {
		            if ($weightKey === $identifier->__toString()) {
		                return $weights[$weightKey];
		            }
		        }
		    }
		}
		else {
		    // get the item the weight should belong to.
		    $assessmentItemRefs = $this->getAssessmentItemRefs();
		    $expectedItemId = $identifier->getPrefix();
		    if (isset($assessmentItemRefs[$expectedItemId])) {
		        $weights = $assessmentItemRefs[$expectedItemId]->getWeights();
		        	
		        if (isset($weights[$identifier->getVariableName()])) {
		            return $weights[$identifier->getVariableName()];
		        }
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
	        $store = $this->getAssessmentItemSessionStore();
	        $items = $this->getAssessmentItemRefs();
	        $sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
	        if ($store->hasAssessmentItemSession($items[$v->getPrefix()], $sequence)) {
	            $session = $store->getAssessmentItemSession($items[$v->getPrefix()], $sequence);
	            return $session->getVariable($v->getVariableName());
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
				
				$store = $this->getAssessmentItemSessionStore();
				$items = $this->getAssessmentItemRefs();
				$sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
				
				if (isset($items[$v->getPrefix()]) && ($session = $store->getAssessmentItemSession($items[$v->getPrefix()], $sequence)) !== false) {
				    return $session[$v->getVariableName()];
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
				$store = $this->getAssessmentItemSessionStore();
				$items = $this->getAssessmentItemRefs();
				$sequence = ($v->hasSequenceNumber() === true) ? $v->getSequenceNumber() - 1 : 0;
				$prefix = $v->getPrefix();
				
				try {
				    if (isset($items[$prefix]) && ($session = $this->getItemSession($items[$prefix], $sequence)) !== false) {
				        $session[$v->getVariableName()] = $value;
				        return;
				    }
				}
				catch (OutOfBoundsException $e) {
				    // The session could be retrieved, but no such variable into it.
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
	 * Begin an item session for a given AssessmentItemRef.
	 * 
	 * @param AssessmentItemRef $assessmentItemRef The AssessmentItemRef you want to session to begin.
	 * @throws OutOfBoundsException If no such AssessmentItemRef is referenced in the route to be taken.
	 */
	protected function beginItemSession(AssessmentItemRef $assessmentItemRef, $occurence = 0) {
	    $assessmentItemRefs = $this->getAssessmentItemRefs();
	    if (isset($assessmentItemRefs[$assessmentItemRef->getIdentifier()]) === true) {
	        $itemSession = new AssessmentItemSession($assessmentItemRef);
	        
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
	 * Initialize the AssessmentItemSession for the whole route.
	 * 
	 */
	protected function initializeItemSessions() {
	    $route = $this->getRoute();
	    $oldPosition = $route->getPosition();
	    
	    foreach ($this->getRoute() as $routeItem) {
	        $itemRef = $routeItem->getAssessmentItemRef();
	        $assessmentSection = $routeItem->getAssessmentSection();
	        $testPart = $routeItem->getTestPart();
	        
	        $navigationMode = $routeItem->getTestPart()->getNavigationMode();
	        $submissionMode = $routeItem->getTestPart()->getSubmissionMode();
	        
	        $session = new AssessmentItemSession($itemRef, $navigationMode, $submissionMode);
	        
	        // Determine the item session control.
	        if ($itemRef->hasItemSessionControl() === true) {
	            $session->setItemSessionControl($itemRef->getItemSessionControl());
	        }
	        else if ($assessmentSection->hasItemSessionControl() === true) {
	            $session->setItemSessionControl($assessmentSection->getItemSessionControl());
	        }
	        else if ($testPart->hasItemSessionControl() === true) {
	            $session->setItemSessionControl($testPart->getItemSessionControl());
	        }
	        // else ... It will be a default one.
	        
	        // Determine the time limits.
	        if ($itemRef->hasTimeLimits() === true) {
	            $session->setTimeLimits($itemRef->getTimeLimits());
	        }
	        else if ($assessmentSection->hasTimeLimits() === true) {
	            $session->setTimeLimits($assessmentSection->getTimeLimits());
	        }
	        else if ($testPart->hasTimeLimits() === true) {
	            $session->setTimeLimits($testPart->getTimeLimits());
	        }
	        // else ... No time limits !
	        
	        $this->addItemSession($session, $routeItem->getOccurence());
	    }
	    
	    $route->setPosition($oldPosition);
	}
	
	public function beginTestSession() {
	    // Initialize item sessions.
	    $this->initializeItemSessions();
	    
	    // Select the eligible items for the candidate.
	    $this->selectEligibleItems();
	    
	    // The test session has now begun.
	    $this->setState(AssessmentTestSessionState::INTERACTING);
	}
	
	/**
	 * Select the eligible items from the current one to the last
	 * following item in the route which is in linear navigation mode.
	 * 
	 * AssessmentItemSession objects related to the eligible items
	 * will be instantiated.
	 * 
	 */
	protected function selectEligibleItems() {
	    $route = $this->getRoute();
	    $oldPosition = $route->getPosition();
	    
	    while($route->valid() === true && $route->isNavigationLinear() === true) {
	        $routeItem = $route->current();
	        $session = $this->getItemSession($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
	        
	        if ($session->getState() === AssessmentItemSessionState::NOT_SELECTED) {
	            $session->beginItemSession();
	        }
	        
	        $route->next();
	    }
	    
	    $route->setPosition($oldPosition);
	}
	
	/**
	 * Add an item session to the current assessment test session.
	 * 
	 * @param AssessmentItemSession $session
	 * @throws LogicException If the AssessmentItemRef object bound to $session is unknown by the AssessmentTestSession.
	 */
	protected function addItemSession(AssessmentItemSession $session, $occurence = 0) {
	    
	    $assessmentItemRefs = $this->getAssessmentItemRefs();
	    $sessionAssessmentItemRefIdentifier = $session->getAssessmentItemRef()->getIdentifier();
	    
	    if ($this->getAssessmentItemRefs()->contains($session->getAssessmentItemRef()) === false) {
	        // The session that is requested to be set is bound to an item
	        // which is not referenced in the test. This is a pure logic error.
	        $msg = "The item session to set is bound to an unknown AssessmentItemRef.";
	        throw new LogicException($msg);
	    }
	    
	    $this->getAssessmentItemSessionStore()->addAssessmentItemSession($session, $occurence);
	}
	
	
	/**
	 * Get an assessment item session.
	 * 
	 * @param AssessmentItemRef $assessmentItemRef
	 * @param integer $occurence
	 * @return AssessmentItemSession|false
	 */
	protected function getItemSession(AssessmentItemRef $assessmentItemRef, $occurence = 0) {
	    
	    $store = $this->getAssessmentItemSessionStore();
	    if ($store->hasAssessmentItemSession($assessmentItemRef, $occurence) === true) {
	        return $store->getAssessmentItemSession($assessmentItemRef, $occurence);
	    }
        
        // No such item session found.
        return false;
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
	
	/**
	 * Get the current Route Item.
	 * 
	 * @return RouteItem|false A RouteItem object or false if the test session is not running.
	 */
	protected function getCurrentRouteItem() {
	    if ($this->isRunning() === true) {
	        return $this->getRoute()->current();
	    }
	    
	    return false;
	}
	
	/**
	 * Get the current AssessmentItemRef.
	 * 
	 * @return AssessmentItemRef|false An AssessmentItemRef object or false if the test session is not running.
	 */
	public function getCurrentAssessmentItemRef() {
	    if ($this->isRunning() === true) {
	        return $this->getCurrentRouteItem()->getAssessmentItemRef();
	    }

	    return false;
	}
	
	/**
	 * Get the current AssessmentItemRef occurence number. In other words
	 * 
	 *  * if the current item of the selection is Q23, the return value is 0.
	 *  * if the current item of the selection is Q01.3, the return value is 2.
	 *  
	 * @return integer| the occurence number of the current AssessmentItemRef in the route or false if the test session is not running.
	 */
	public function getCurrentAssessmentItemRefOccurence() {
	    if ($this->isRunning() === true) {
	        return $this->getCurrentRouteItem()->getOccurence();
	    }
	    
	    return false;
	}
	
	/**
	 * Get the current AssessmentSection.
	 * 
	 * @return AssessmentSection|false An AssessmentSection object or false if the test session is not running.
	 */
	public function getCurrentAssessmentSection() {
	    if ($this->isRunning() === true) {
	        return $this->getCurrentRouteItem()->getAssessmentSection();
	    }
	   
	    return false;
	}
	
	/**
	 * Get the current TestPart.
	 * 
	 * @return TestPart A TestPart object or false if the test session is not running.
	 */
	public function getCurrentTestPart() {
	    if ($this->isRunning() === true) {
	        return $this->getCurrentRouteItem()->getTestPart();
	    }
	    
	    return false;
	}
	
	/**
	 * Get the current navigation mode.
	 * 
	 * @return integer|false A value from the NavigationMode enumeration or false if the test session is not running.
	 */
	public function getCurrentNavigationMode() {
	    if ($this->isRunning() === true) {
	        return $this->getCurrentTestPart()->getNavigationMode();
	    }
	    
	    return false;
	}
	
	/**
	 * Get the current submission mode.
	 * 
	 * @return integer|false A value from the SubmissionMode enumeration or false if the test session is not running.
	 */
	public function getCurrentSubmissionMode() {
	    if ($this->isRunning() === true) {
	        return $this->getCurrentTestPart()->getSubmissionMode();
	    }
	    
	    return false;
	}
	
	/**
	 * Get the number of remaining item for the current item in the route.
	 * 
	 * @return integer|false -1 if the item is adaptive but not completed, otherwise the number of remaining attempts. If the assessment test session is not running, false is returned.
	 */
	public function getCurrentRemainingAttempts() {
	    if ($this->isRunning() === true) {
	        $routeItem = $this->getCurrentRouteItem();
	        $session = $this->getItemSession($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
	        return $session->getRemainingAttempts();
	    }
	    
	    return false;
	}
	
	/**
	 * Skip the current item. A call to moveNext() is automatically performed.
	 * 
	 * @throws AssessmentItemSessionException If the current item cannot be skipped or if timings are not respected.
	 * @throws AssessmentTestSessionException If the test session is not running.
	 */
	public function skip() {
	    
	    if ($this->isRunning() === false) {
	        $msg = "Cannot skip the current item while the state of the test session is INITIAL or CLOSED.";
	        throw new AssessmentTestSessionException(msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    $routeItem = $this->getCurrentRouteItem();
	    $session = $this->getItemSession($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
	    $session->skip();
	    
	    $this->nextRouteItem();
	}
	
	/**
	 * Begin an attempt for the current item in the route.
	 * 
	 * @throws AssessmentTestSessionException If the time limits are already exceeded or if there are no more attempts allowed.
	 */
	public function beginAttempt() {
	    if ($this->isRunning() === false) {
	        $msg = "Cannot begin an attempt for the current item while the state of the test session is INITIAL or CLOSED.";
	        throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    $routeItem = $this->getCurrentRouteItem();
	    $session = $this->getItemSession($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
	    $session->beginAttempt();
	}
	
	/**
	 * End an attempt for the current item in the route. If the current navigation mode
	 * is LINEAR, the TestSession moves automatically to the next step in the route or
	 * the end of the session if the responded item is the last one.
	 * 
	 * @param State $responses
	 * @throws AssessmentTestSessionException
	 * @throws AssessmentItemSessionException
	 */
	public function endAttempt(State $responses) {
	    if ($this->isRunning() === false) {
	        $msg = "Cannot end an attempt for the current item while the state of the test session is INITIAL or CLOSED.";
	        throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    $routeItem = $this->getCurrentRouteItem();
	    $session = $this->getItemSession($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
	    $session->endAttempt($responses);
	    
	    // Update the lastly updated item occurence.
	    $this->notifyLastOccurenceUpdate($routeItem->getAssessmentItemRef(), $routeItem->getOccurence());
	    
	    if ($this->getCurrentNavigationMode() === NavigationMode::LINEAR) {
	        // Go automatically to the next step in the route.
	        $this->moveNext();
	    }
	}
	
	/**
	 * Ask the test session for moving the next assessment item to be taken by candidate, with respect
	 * to the preConditions and branchRules of the assessment test.
	 * 
	 * @throws AssessmentTestSessionException If the test session is not running or an error occurs during the transition.
	 */
	public function moveNext() {
	    if ($this->isRunning() === false) {
	        $msg = "Cannot move to the next item while the test session state is INITIAL or CLOSED.";
	        throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    $this->nextRouteItem();
	}
	
	/**
	 * Move to the next item in the route.
	 * 
	 * @throws AssessmentTestSessionException If the test session is not running.
	 */
	protected function nextRouteItem() {
	    
	    if ($this->isRunning() === false) {
	        $msg = "Cannot move to the next position while the state of the test session is INITIAL or CLOSED.";
	        throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    $route = $this->getRoute();
	    $route->next();
	    
	    if ($route->valid() === false) {
	        // This is the end of the test session.
	        // 1. Apply outcome processing.
	        $this->outcomeProcessing();
	        
	        // 2. End the test session.
	        $this->endTestSession();
	    }
	}
	
	/**
	 * Apply outcome processing at test-level.
	 * 
	 * @throws AssessmentTestSessionException If the test is not at its last route item or if an error occurs at OutcomeProcessing time.
	 */
	protected function outcomeProcessing() {
	    if ($this->getRoute()->isLast() === false) {
	        $msg = "Outcome Processing may be applied only if the current route item is the last one of the route.";
	        throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    if ($this->getAssessmentTest()->hasOutcomeProcessing() === true) {
	        // As per QTI Spec:
	        // The values of the test's outcome variables are always reset to their defaults prior
	        // to carrying out the instructions described by the outcomeRules.
	        $this->resetOutcomeVariables();
	         
	        $outcomeProcessing = $this->getAssessmentTest()->getOutcomeProcessing();
	        
	        try {
	            $outcomeProcessingEngine = new OutcomeProcessingEngine($outcomeProcessing, $this);
	            $outcomeProcessingEngine->process();
	        }
	        catch (ProcessingException $e) {
	            $msg = "An error occured while processing OutcomeProcessing.";
	            throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::OUTCOME_PROCESSING_ERROR, $e);
	        }
	    }
	}
	
	/**
	 * Whether the test session is running. In other words, if the test session is not in
	 * state INITIAL nor CLOSED.
	 * 
	 * @return boolean Whether the test session is running.
	 */
	public function isRunning() {
	    return $this->getState() !== AssessmentTestSessionState::INITIAL && $this->getState() !== AssessmentTestSessionState::CLOSED;
	}
	
	/**
	 * End the test session.
	 * 
	 * @throws AssessmentTestSessionException If the test session is already CLOSED or is in INITIAL state.
	 */
	public function endTestSession() {
	    
	    if ($this->isRunning() === false) {
	        $msg = "Cannot end the test session while the state of the test session is INITIAL or CLOSED.";
	        throw new AssessmentTestSessionException($msg, AssessmentTestSessionException::STATE_VIOLATION);
	    }
	    
	    $this->setState(AssessmentTestSessionState::CLOSED);
	}
	
	/**
	 * Get the item sessions held by the test session by item reference $identifier.
	 * 
	 * @param string $identifier An item reference $identifier e.g. Q04. Prefixed or sequenced identifiers e.g. Q04.1.X are considered to be malformed.
	 * @return AssessmentItemSessionCollection|false A collection of AssessmentItemSession objects or false if no item session could be found for $identifier.
	 * @throws InvalidArgumentException If the given $identifier is malformed.
	 */
	public function getAssessmentItemSessions($identifier) {
	    try {
	        $v = new VariableIdentifier($identifier);
	        
	        if ($v->hasPrefix() === true || $v->hasSequenceNumber() === true) {
	            $msg = "'${identifier}' is not a valid item reference identifier.";
	            throw new InvalidArgumentException($msg, 0, $e);
	        }
	        
	        $itemRefs = $this->getAssessmentItemRefs();
	        if (isset($itemRefs[$identifier]) === false) {
	            return false;
	        }
	        
	        try {
	            return $this->getAssessmentItemSessionStore()->getAssessmentItemSessions($itemRefs[$identifier]);
	        }
	        catch (OutOfBoundsException $e) {
	            return false;
	        }
	    }
	    catch (InvalidArgumentException $e) {
	        $msg = "'${identifier}' is not a valid item reference identifier.";
	        throw new InvalidArgumentException($msg, 0, $e);
	    }
	}
	
	/**
	 * Get a subset of AssessmentItemRef objects involved in the test session.
	 * 
	 * @param string $sectionIdentifier An optional section identifier.
	 * @param IdentifierCollection $includeCategories The optional item categories to be included in the subset.
	 * @param IdentifierCollection $excludeCategories The optional item categories to be excluded from the subset.
	 * @return AssessmentItemRefCollection A collection of AssessmentItemRef objects that match all the given criteria.
	 */
	public function getItemSubset($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null) {
	    return $this->getRoute()->getAssessmentItemRefsSubset($sectionIdentifier, $includeCategories, $excludeCategories);
	}
	
	/**
	 * Get the number of items in the current Route. In other words, the total number
	 * of item occurences the candidate can take during the test.
	 * 
	 * @return integer
	 */
	public function getRouteCount() {
	    return $this->getRoute()->count();
	}
	
	/**
	 * Get the map of last occurence updates.
	 * 
	 * @return SplObjectStorage A map.
	 */
	protected function getLastOccurenceUpdate() {
		return $this->lastOccurenceUpdate;
	}
	
	/**
	 * Set the map of last occurence updates.
	 * 
	 * @param SplObjectStorage $lastOccurenceUpdate A map.
	 */
	protected function setLastOccurenceUpdate(SplObjectStorage $lastOccurenceUpdate) {
		$this->lastOccurenceUpdate = $lastOccurenceUpdate;
	}
	
	/**
	 * Notify which $occurence of $assessmentItemRef was the last updated.
	 * 
	 * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
	 * @param integer $occurence An occurence number for $assessmentItemRef.
	 */
	protected function notifyLastOccurenceUpdate(AssessmentItemRef $assessmentItemRef, $occurence) {
		$lastOccurenceUpdate = $this->getLastOccurenceUpdate();
		$lastOccurenceUpdate[$assessmentItemRef] = $occurence;
	}
	
	/**
	 * Returns which occurence of item was lastly updated.
	 * 
	 * @param AssessmentItemRef|string $assessmentItemRef An AssessmentItemRef object.
	 * @return int|false The occurence number of the lastly updated item session for the given $assessmentItemRef or false if no occurence was updated yet.
	 */
	public function whichLastOccurenceUpdate($assessmentItemRef) {
		if (is_string($assessmentItemRef) === true) {
			$assessmentItemRefs = $this->getAssessmentItemRefs();
			if (isset($assessmentItemRefs[$assessmentItemRef]) === true) {
				$assessmentItemRef = $assessmentItemRefs[$assessmentItemRef];
			}
		}
		else if (!$assessmentItemRef instanceof AssessmentItemRef) {
			$msg = "The 'assessmentItemRef' argument must be a string or an AssessmentItemRef object.";
			throw new InvalidArgumentException($msg);
		}
		
		$lastOccurenceUpdate = $this->getLastOccurenceUpdate();
		if (isset($lastOccurenceUpdate[$assessmentItemRef]) === true) {
			return $lastOccurenceUpdate[$assessmentItemRef];
		}
		else {
			return false;
		}
	}
	
	/**
	 * Instantiate a new AssessmentItemSession from an AssessmentTest object.
	 * 
	 * @param AssessmentTest $assessmentTest The AssessmentTest to be instantiated as a new AssessmentTestSession object.
	 * @return AssessmentTestSession An instantiated AssessmentTestSession object.
	 */
	public static function instantiate(AssessmentTest $assessmentTest) {
	    // 1. Apply selection and ordering.
	    $routeStack = array();
	    
	    foreach ($assessmentTest->getTestParts() as $testPart) {
	       
	        foreach ($testPart->getAssessmentSections() as $assessmentSection) {
	            $trail = array();
	            $mark = array();
	            $visibleSectionStack = array();
	            
	            array_push($trail, $assessmentSection);
	            
	            while (count($trail) > 0) {
	               
	                $current = array_pop($trail);
	                
	                if (!in_array($current, $mark, true) && $current instanceof AssessmentSection) {
	                    // 1st pass on assessmentSection.
	                    $currentAssessmentSection = $current;
	                    
	                    if ($currentAssessmentSection->isVisible() === true) {
	                        array_push($visibleSectionStack, $currentAssessmentSection);
	                    }
	                    
	                    array_push($mark, $current);
	                    array_push($trail, $current);
	                    
	                    foreach (array_reverse($current->getSectionParts()->getArrayCopy()) as $sectionPart) {
	                        array_push($trail, $sectionPart);
	                    }
	                }
	                else if (in_array($current, $mark, true)) {
	                    // 2nd pass on assessmentSection.
	                    // Pop N routeItems where N is the children count of $current.
	                    $poppedRoutes = array();
	                    for ($i = 0; $i < count($current->getSectionParts()); $i++) {
	                        $poppedRoutes[] = array_pop($routeStack);
	                    }
	                    
	                    $selection = new BasicSelection($current, new SelectableRouteCollection(array_reverse($poppedRoutes)));
	                    $selectedRoutes = $selection->select();
	                    
	                    // The last visible AssessmentSection from the top to the bottom of the tree
	                    // is useful to know which RubrikBlock to apply on selected RouteItems
	                    $lastVisible = array_pop($visibleSectionStack);
	                    if (count($visibleSectionStack) === 0) {
	                        // top-level AssessmentSection, the visible container is actually the TestPart it belongs to.
	                        $lastVisible = $testPart;
	                    }
	                    
	                    // Shuffling can be applied on selected routes.
	                    // $route will contain the final result of the selection + ordering.
	                    $ordering = new BasicOrdering($current, $selectedRoutes);
	                    $selectedRoutes = $ordering->order();
	                    
                        $route = new SelectableRoute($current->isFixed(), $current->isRequired(), $current->isVisible(), $current->mustKeepTogether());
	                    foreach ($selectedRoutes as $r) {
	                        $route->appendRoute($r);
	                    }
	                    
	                    // Add to the last item of the selection the branch rules of the AssessmentSection/testPart
	                    // on which the selection is applied.
	                    $lastRouteItem = $route->getLastRouteItem();
	                    $lastRouteItem->addBranchRules($current->getBranchRules());
	                    
	                    array_push($routeStack, $route);
	                }
	                else if ($current instanceof AssessmentItemRef) {
	                    // leaf node.
	                    $route = new SelectableRoute($current->isFixed(), $current->isRequired());
	                    $route->addRouteItem($current, $currentAssessmentSection, $testPart);
	                    array_push($routeStack, $route);
	                }
	            }
	        }
	    }
	    
	    $finalRoutes = $routeStack;
	    $route = new SelectableRoute();
	    foreach ($finalRoutes as $finalRoute) {
	        $route->appendRoute($finalRoute);
	    }
	    
	    return new AssessmentTestSession($assessmentTest, $route);
	}
}