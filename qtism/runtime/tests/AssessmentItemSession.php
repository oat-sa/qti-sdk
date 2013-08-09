<?php

namespace qtism\runtime\tests;

use qtism\data\TimeLimits;
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
use \DateTime;
use \InvalidArgumentException;

/**
 * The AssessmentItemSession class implements the lifecycle of an AssessmentItem session.
 * 
 * When instantiated, the resulting AssessmentItemSession object is initialized in
 * the in the AssessmentItemSessionState::INITIAL state, and the session begins (a call
 * to AssessmentItemSession::beginItemSession is performed).
 * 
 * FROM IMS QTI:
 * 
 * An item session is the accumulation of all the attempts at a particular instance of an 
 * item made by a candidate. In some types of test, the same item may be presented to the 
 * candidate multiple times (e.g., during 'drill and practice'). Each occurrence or 
 * instance of the item is associated with its own item session.
 * 
 * The following diagram illustrates the user-perceived states of the item session. Not all 
 * states will apply to every scenario, for example feedback may not be provided for an 
 * item or it may not be allowed in the context in which the item is being used. Similarly, 
 * the candidate may not be permitted to review their responses and/or examine a model 
 * solution. In practice, systems may support only a limited number of the indicated state 
 * transitions and/or support other state transitions not shown here.
 * 
 * For system developers, an important first step in determining which requirements apply 
 * to their system is to identify which of the user-perceived states are supported in their 
 * system and to match the state transitions indicated in the diagram to their own event 
 * model.
 * 
 * The discussion that follows forms part of this specification's requirements on Delivery 
 * Engines.
 * 
 * The session starts when the associated item first becomes eligible for delivery to the 
 * candidate. The item session's state is then maintained and updated in response to the 
 * actions of the candidate until the session is over. At any time the state of the session 
 * may be turned into an itemResult. A delivery system may also allow an itemResult to be 
 * used as the basis for a new session in order to allow a candidate's responses to be seen 
 * in the context of the item itself (and possibly compared to a solution) or even to allow 
 * a candidate to resume an interrupted session at a later time.
 * 
 * The initial state of an item session represents the state after it has been determined 
 * that the item will be delivered to the candidate but before the delivery has taken 
 * place.
 * 
 * In a typical non-Adaptive Test the items are selected in advance and the candidate's 
 * interaction with all items is reported at the end of the test session, regardless of 
 * whether or not the candidate actually attempted all the items. In effect, item sessions 
 * are created in the initial state for all items at the start of the test and are 
 * maintained in parallel. In an Adaptive Test the items that are to be presented are 
 * selected during the session based on the responses and outcomes associated with the 
 * items presented so far. Items are selected from a large pool and the delivery engine 
 * only reports the candidate's interaction with items that have actually been selected.
 * 
 * A candidate's interaction with an item is broken into 0 or more attempts. During each 
 * attempt the candidate interacts with the item through one or more candidate sessions. 
 * At the end of a candidate session the item may be placed into the suspended state ready 
 * for the next candidate session. During a candidate session the item session is in the 
 * interacting state. Once an attempt has ended response processing takes place, after 
 * response processing a new attempt may be started.
 * 
 * For non-adaptive items, response processing typically takes place a limited number of 
 * times, usually only once. For adaptive items, no such limit is required because the 
 * response processing adapts the values it assigns to the outcome variables based on the 
 * path through the item. In both cases, each invocation of response processing occurrs at 
 * the end of each attempt. The appearance of the item's body, and whether any modal 
 * feedback is shown, is determined by the values of the outcome variables.
 * 
 * When no more attempts are allowed the item session passes into the closed state. Once in 
 * the closed state the values of the response variables are fixed. A delivery system or 
 * reporting tool may still allow the item to be presented after it has reached the closed 
 * state. This type of presentation takes place in the review state, summary feedback may 
 * also be visible at this point if response processing has taken place and set a suitable 
 * outcome variable.
 * 
 * Finally, for systems that support the display of solutions, the item session may pass 
 * into the solution state. In this state, the candidate's responses are temporarily 
 * replaced by the correct values supplied in the corresponding responseDeclarations 
 * (or NULL if none was declared).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentItemSession extends State {
	
    /**
     * The item completion status 'incomplete'.
     * 
     * @var string
     */
    const COMPLETION_STATUS_INCOMPLETE = 'incomplete';
    
    /**
     * The item completion status 'not_attempted'.
     * 
     * @var string
     */
    const COMPLETION_STATUS_NOT_ATTEMPTED = 'not_attempted';
    
    /**
     * The item completion status 'unknown'.
     * 
     * @var string
     */
    const COMPLETION_STATUS_UNKNOWN = 'unknown';
    
    /**
     * The item completion status 'completed'.
     * 
     * @var string
     */
    const COMPLETION_STATUS_COMPLETED = 'completed';
    
    /**
     * A timing reference used to compute the duration of 
     * the session.
     * 
     * @var DateTime
     */
    private $timeReference;
    
    /**
     * An acceptable latency to be applied
     * on duration when timelimits are in
     * force.
     */
    private $acceptableLatency;
    
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
	 * The time limits to be applied on the session if
	 * needed.
	 * 
	 * @var TimeLimits
	 */
	private $timeLimits = null;
	
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
		
		// Acceptable latency time is by default "0 seconds". In
		// other words, no additional time is given.
		$this->setAcceptableLatency(new Duration('PT0S'));
		$this->setState(AssessmentItemSessionState::INITIAL);
		$this->beginItemSession();
		
		// Set-up default ItemSessionControl object.
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
	 * Set the TimeLimits to be applied to the session.
	 * 
	 * @param TimeLimits $timeLimits A TimeLimits object or null if no time limits must be applied.
	 */
	public function setTimeLimits(TimeLimits $timeLimits = null) {
	    $this->timeLimits = $timeLimits;
	}
	
	/**
	 * Get the TimeLimits to be applied to the session.
	 * 
	 * @return TimeLimits A TimLimits object or null if no time limits must be applied.
	 */
	public function getTimeLimits() {
	    return $this->timeLimits;
	}
	
	/**
	 * Set the timing reference.
	 * 
	 * @param DateTime $timeReference A DateTime object.
	 */
	protected function setTimeReference(DateTime $timeReference) {
	    $this->timeReference = $timeReference;
	}
	
	/**
	 * Get the timing reference.
	 * 
	 * @return DateTime A DateTime object.
	 */
	protected function getTimeReference() {
	    return $this->timeReference;
	}
	
	/**
	 * Set the acceptable latency time to be applied
	 * when timelimits are in force.
	 * 
	 * @param Duration $acceptableLatency A Duration object.
	 */
	public function setAcceptableLatency(Duration $acceptableLatency) {
	    $this->acceptableLatency = $acceptableLatency;
	}
	
	/**
	 * Get the acceptable latency time to be applied when timelimits
	 * are in force.
	 * 
	 * @return Duration A Duration object.
	 */
	public function getAcceptableLatency() {
	    return $this->acceptableLatency;
	}
	
	/**
	 * Whether the session is driven by a TimeLimits object
	 * or not.
	 * 
	 * @return boolean
	 */
	public function hasTimeLimits() {
	    return !is_null($this->getTimeLimits());
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
		$this->setVariable(new ResponseVariable('numAttempts', Cardinality::SINGLE, BaseType::INTEGER));
		$this->setVariable(new ResponseVariable('duration', Cardinality::SINGLE, BaseType::DURATION));
		 
		// -- Create the built-in outcome variables.
		$this->setVariable(new OutcomeVariable('completionStatus', Cardinality::SINGLE, BaseType::IDENTIFIER, self::COMPLETION_STATUS_UNKNOWN));
		
		// The session gets the INITIAL state, ready for a first attempt.
		$this->setState(AssessmentItemSessionState::INITIAL);
		$this['duration'] = new Duration('PT0S');
		$this['numAttempts'] = 0;
		$this['completionStatus'] = self::COMPLETION_STATUS_NOT_ATTEMPTED;
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
	    if ($this['completionStatus'] === self::COMPLETION_STATUS_COMPLETED) {
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
			
			$this['duration'] = new Duration('PT0S');
		}
		
		$this['numAttempts'] = $this['numAttempts'] + 1;
		
		// The session get the INTERACTING STATE.
		$this->setState(AssessmentItemSessionState::INTERACTING);
		
		// Register a time reference that will be used later on to compute the duration built-in variable.
		$this->setTimeReference(new DateTime());
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
		if ($this->getAssessmentItemRef()->isAdaptive() === true && $this['completionStatus'] === self::COMPLETION_STATUS_COMPLETED) {
		    
		    $this->endItemSession();
		}
		else if ($this->getAssessmentItemRef()->isAdaptive() === false && $this['numAttempts'] >= $this->getItemSessionControl()->getMaxAttempts()) {
		    
		    $this->endItemSession();
		    $this['completionStatus'] = self::COMPLETION_STATUS_COMPLETED;
		}
		else {
		    // Waiting for a next attempt.
		    $this->suspend();
		}
	}
	
	/**
	 * Suspend the item session. The state will switch to SUSPENDED.
	 * 
	 */
	public function suspend() {
	    // If the current state is INTERACTING update duration built-in variable.
	    if ($this->getState() === AssessmentItemSessionState::INTERACTING) {
	        $timeRef = $this->getTimeReference();
	        $now = new DateTime();
	        $this['duration']->add($timeRef->diff($now));
	        $this->setTimeReference(new DateTime());
	    }
	    
	    $this->setState(AssessmentItemSessionState::SUSPENDED);
	}
	
	/**
	 * Close the item session because no more attempts are allowed. The 'completionStatus' built-in outcome variable
	 * becomes 'completed', and the state of the item session becomes 'closed'.
	 * 
	 * The item session ends if:
	 * 
	 * * the candidate ends an attempt, the item is non-adaptive and the maximum amount of attempts is reached.
	 * * the candidate ends an attempt, the is is adaptive and the completionStatus is 'complete'.
	 */
	public function endItemSession() {
	    
	    // If the candidate was interacting, suspend before
	    // to get a correct state flow.
	    if ($this->getState() === AssessmentItemSessionState::INTERACTING) {
	        $this->suspend();
	    }
	   
		$this->setState(AssessmentItemSessionState::CLOSED);
	}
}