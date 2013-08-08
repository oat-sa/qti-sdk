<?php

namespace qtism\runtime\tests;

use qtism\runtime\processing\ResponseProcessingEngine;
use qtism\runtime\common\OutcomeVariable;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ItemSessionControl;
use qtism\common\datatypes\Duration;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\common\State;
use \InvalidArgumentException;

class AssessmentItemSession extends State {
	
	/**
	 * The state of the Item Session as described
	 * by the AssessmentItemSessionState enumeration.
	 * 
	 * @var integer
	 */
	private $state = AssessmentItemSessionState::INITIAL;
	
	/**
	 * The ItemSessionControl object giving information about how to control
	 * the session.
	 * 
	 * @var ItemSessionControl
	 */
	private $itemSessionControl;
	
	/**
	 * The ExtendedAssessmentItemRef describing the item the session
	 * handles.
	 * 
	 * @var ExtendedAssessmentItemRef
	 */
	private $assessmentItemRef;
	
	/**
	 * Create a new AssessmentItemSession object.
	 * 
	 * * Unless provided in the $variables array, the built-in response/outcome variables 'numAttempts', 'duration' and
	 * 'completionStatus' will be created and set to an appropriate default value automatically.
	 * 
	 * * The AssessmentItemSession object is set up with a default ItemSessionControl object. If you want a specific ItemSessionControl object to rule the session, use the setItemSessionControl() method.
	 * * The AssessmentItemSession object is set up with a default INITIAL state. Built-in outcome/response variables are then set.
	 * 
	 * After the instantiation of the AssessmentItemSession object, you can update the values of variables accordingly, and set up
	 * the current state of the session.
	 * 
	 * @param ExtendedAssessmentItemRef $assessmentItemRef The description of the item that the session handles.
	 */
	public function __construct(ExtendedAssessmentItemRef $assessmentItemRef) {
		parent::__construct();
		$this->setAssessmentItemRef($assessmentItemRef);
		$this->setState(AssessmentItemSessionState::INITIAL);
		$this->beginItemSession();
		
		// Set-up default ItemAssessmentControl object.
		$this->setItemSessionControl(new ItemSessionControl());
	}
	
	/**
	 * Set the state of the current AssessmentItemSession.
	 * 
	 * @param integer $state A value from the AssessmentItemSessionState enumeration.
	 * @throws InvalidArgumentException If $state is not a value from the AssessmentItemSessionState enumeration.
	 */
	public function setState($state) {
		if (in_array($state, AssessmentItemSessionState::asArray())) {
			$this->state = $state;
		}
		else {
			$msg = "The state argument must be a value from the AssessmentItemSessionState enumeration.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the state of the current AssessmentItemSession.
	 * 
	 * @return integer A value from the AssessmentItemSessionState enumeration.
	 */
	public function getState() {
		return $this->state;
	}
	
	/**
	 * Set the ItemSessionControl object which describes the way to control
	 * the item session.
	 * 
	 * @param ItemSessionControl $itemSessionControl An ItemSessionControl object.
	 */
	public function setItemSessionControl(ItemSessionControl $itemSessionControl) {
		$this->itemSessionControl = $itemSessionControl;
	}
	
	/**
	 * Get the ItemSessionControl object which describes the way to control the item session.
	 * 
	 * @return ItemSessionControl An ItemSessionControl object.
	 */
	public function getItemSessionControl() {
		return $this->itemSessionControl;
	}
	
	/**
	 * Set the ExtendendedAssessmentItemRef object which describes the item to be handled
	 * by the session.
	 * 
	 * @param ExtendedAssessmentItemRef $assessmentItemRef An ExtendedAssessmentItemRef object.
	 */
	public function setAssessmentItemRef(ExtendedAssessmentItemRef $assessmentItemRef) {
	    $this->assessmentItemRef = $assessmentItemRef;
	}
	
	/**
	 * Get the ExtendedAssessmentItemRef object which describes the item to be handled by the
	 * session.
	 * 
	 * @return ExtendedAssessmentItemRef An ExtendedAssessmentItemRef object.
	 */
	public function getAssessmentItemRef() {
	    return $this->assessmentItemRef;
	}
	
	/**
	 * Start the item session. The item session is started when the related item
	 * becomes eligible for the candidate.
	 * 
	 * * ResponseVariable objects involved in the session will be set a value of NULL.
	 * * OutcomeVariable objects involved in the session will be set their default value if any. Otherwise, they are set to NULL unless their baseType is integer or float. In this case, the value is 0 or 0.0.
	 * * The state of the session is set to INITIAL.
	 * 
	 */
	public function beginItemSession() {
	    
		// We initialize the item session and its variables.
		foreach ($this->getAssessmentItemRef()->getOutcomeDeclarations() as $outcomeDeclaration) {
		    // Outcome variables are instantiantiated as part of the item session.
		    // Their values may be initialized with a default value if they have one.
		    $outcomeVariable = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		    $outcomeVariable->initialize();
		    $outcomeVariable->applyDefaultValue();
		    $this->setVariable($outcomeVariable);
		}
		
		foreach ($this->getAssessmentItemRef()->getResponseDeclarations() as $responseDeclaration) {
		    // Response variables are instantiated as part of the item session.
		    // Their values are always initialized to NULL.
		    $responseVariable = ResponseVariable::createFromDataModel($responseDeclaration);
		    $responseVariable->initialize();
		    $this->setVariable($responseVariable);
		}
		
		// -- Create the built-in response variables.
		$this->setVariable(new ResponseVariable('numAttempts', Cardinality::SINGLE, BaseType::INTEGER, 0));
		$this->setVariable(new ResponseVariable('duration', Cardinality::SINGLE, BaseType::DURATION, new Duration('PT0S')));
		 
		// -- Create the built-in outcome variables.
		$this->setVariable(new OutcomeVariable('completionStatus', Cardinality::SINGLE, BaseType::IDENTIFIER, 'unknown'));
		
		// The session gets the INITIAL state, ready for a first attempt.
		$this->setState(AssessmentItemSessionState::INITIAL);
		$this['numAttempts'] = 0;
		$this['duration'] = new Duration('PT0S');
		$this['completionStatus'] = 'not_attempted';
	}
	
	/**
	 * begin an attempt for this item session.
	 * 
	 * If the attempt to begin is the first one of the session:
	 * 
	 * * ResponseVariables are applied their default value.
	 * * The completionStatus variable changes to 'unknown'.
	 *
	 */
	public function beginAttempt() {
	    
	    // Are we allowed to begin a new attempt?
	    $itemRef = $this->getAssessmentItemRef();
	    if ($this['completionStatus'] === 'completed') {
	        $identifier = $itemRef->getIdentifier();
	        $msg = "A new attempt for item '${identifier}' is not allowed. The item's ";
	        $msg.= "completion status is already set to 'complete'";
	        throw new AssessmentItemSessionException($msg, AssessmentItemSessionException::MAX_ATTEMPTS_EXCEEDED);
	    }
	    else if ($itemRef->isAdaptive() === false && $this['numAttempts'] >= $this->getItemSessionControl()->getMaxAttempts()) {
	        $msg = "A new attempt for item '${identifier}' is not allowed. The item's maximum attempts is already reached.";
	        throw new AssessmentItemSessionException($msg, AssessmentItemSessionException::MAX_ATTEMPTS_EXCEEDED);
	    }
	    
		$data = &$this->getDataPlaceHolder();
		
		// Response variables' default values are set at the beginning
		// of the first attempt.
		if ($this['numAttempts'] === 0) {
		    
		    // At the start of the first attempt, the completionStatus
		    // goes to 'unknown' and response variables get their
		    // default values if any.
		    
			foreach (array_keys($data) as $k) {
				if ($data[$k] instanceof ResponseVariable) {
					$data[$k]->applyDefaultValue();
				}
			}
		}
		
		$this['numAttempts'] = $this['numAttempts'] + 1;
		
		// The session get the INTERACTING STATE.
		$this->setState(AssessmentItemSessionState::INTERACTING);
	}
	
	/**
	 * End the attempt by providing responses or by another action. If $responses
	 * is provided, the values found into it will be merged to the current state
	 * before ResponseProcessing is executed.
	 * 
	 * * If more attempts are allowed, the session continues.
	 * 
	 * @param State $responses (optional) A State composed by the candidate's responses to the item.
	 * @param boolean $responseProcessing (optional) Whether to execute the responseProcessing or not.
	 */
	public function endAttempt(State $responses = null, $responseProcessing = true) {
		
	    // Apply the responses to the current state and process the responses.
	    if (is_null($responses) !== true) {
	        foreach ($responses as $identifier => $value) {
	            $this[$identifier] = $value->getValue();
	        }   
	    }
	    
	    if ($responseProcessing === true) {
	        $responseProcessing = $this->getAssessmentItemRef()->getResponseProcessing();
	        $engine = new ResponseProcessingEngine($responseProcessing, $this);
	        $engine->process();
	    }
	    
	    
		// After response processing, if the item is adaptive, close
		// the item session if completionStatus = 'complete'.
		if ($this->getAssessmentItemRef()->isAdaptive() === true && $this['completionStatus'] === 'completed') {
		    $this->endItemSession();
		}
		else if ($this->getAssessmentItemRef()->isAdaptive() === false) {
		    
		    $maxAttempts = $this->getItemSessionControl()->getMaxAttempts();
		    
		    if ($this['numAttempts'] >= $maxAttempts) {
		        $this->endItemSession();
		        $this['completionStatus'] = 'completed';
		    }
		}
		// else ...
		// The item is adaptive but not 'completed', the session still lives.
	}
	
	/**
	 * Close the item session because no more attempts are allowed. The 'completionStatus' built-in outcome variable
	 * becomes 'complete', and the state of the item session becomes 'closed'.
	 * 
	 * The item session ends if:
	 * 
	 * * the candidate ends an attempt, the item is non-adaptive and the maximum amount of attempts is reached.
	 * * the candidate ends an attempt, the is is adaptive and the completionStatus is 'complete'.
	 */
	public function endItemSession() {
		$this->setState(AssessmentItemSessionState::CLOSED);
	}
}