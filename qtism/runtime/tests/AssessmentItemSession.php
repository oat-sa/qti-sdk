<?php

namespace qtism\runtime\tests;

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
	private $state;
	
	/**
	 * The ItemSessionControl object giving information about how to control
	 * the session.
	 * 
	 * @var ItemSessionControl
	 */
	private $itemSessionControl;
	
	/**
	 * Create a new AssessmentItemSession object.
	 * 
	 * * Unless provided in the $variables array, the built-in response/outcome variables 'numAttempts', 'duration' and
	 * 'completionStatus' will be created and set to an appropriate default value automatically.
	 * 
	 * * The AssessmentItemSession object is set up with a default ItemSessionControl object. If you want a specific ItemSessionControl
	 * object to rule the session, use the setItemSessionControl() method.
	 * 
	 * @param array $variables An array of Variable objects to compose the AssessmentItemSession.
	 * @param integer $state A value from the AssessmentItemSessionState enumeration representing the current state of the AssessmentItemSession.
	 */
	public function __construct(array $variables = array(), $state = AssessmentItemSessionState::INITIAL) {
		parent::__construct($variables);
		$this->setState($state);
		
		// Set-up default ItemAssessmentControl object.
		$this->setItemSessionControl(new ItemSessionControl());
		
		// -- Create the built-in response variables.
		if (isset($this['numAttempts']) === false) {
			$this->setVariable(new ResponseVariable('numAttempts', Cardinality::SINGLE, BaseType::INTEGER, 0));
		}
		
		if (isset($this['duration']) === false) {
			$this->setVariable(new ResponseVariable('duration', Cardinality::SINGLE, BaseType::DURATION, new Duration('PT0S')));
		}
		
		// -- Create the built-in outcome variables.
		if (isset($this['completionStatus']) === false) {
			$this->setVariable(new OutcomeVariable('completionStatus', Cardinality::SINGLE, BaseType::STRING, 'unknown'));
		}	
	}
	
	/**
	 * Set the state of the current AssessmentItemSession.
	 * 
	 * @param integer $state A value from the AssessmentItemSessionState enumeration.
	 * @throws InvalidArgumentException If $state is not a value from the AssessmentItemSessionState enumeration.
	 */
	protected function setState($state) {
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
		return $this->itemSessionControl();
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
	public function beginSession() {
		
		// We will initialize the item session and its variables.
		// Be carefull, do not touch 'numAttempts', 'duration' and 'completionStatus'
		// variables, they are already correctly initialized.
		
		$data = &$this->getDataPlaceHolder();
		foreach (array_keys($data) as $k) {
			if (!in_array($k, array('numAttempts', 'duration')) && $data[$k] instanceof ResponseVariable) {
				// Response variables are instantiated as part of the item session.
				// Their values are always initialized to NULL.
				$data[$k]->initialize();
			}
			else if ($k !== 'completionStatus') {
				// Outcome variables are instantiantiated as part of the item session.
				// Their values may be initialized with a default value.
				$data[$k]->initialize();
				$data[$k]->applyDefaultValue();
			}
		}
		
		$this->setState(AssessmentItemSessionState::INITIAL);
		$this['numAttempts'] = 0;
		$this['duration'] = new Duration('PT0S');
		$this['completionStatus'] = 'not_attempted';
	}
	
	/**
	 * begin an attempt for this item session.
	 *
	 */
	public function beginAttempt() {
		$data = &$this->getDataPlaceHolder();
		
		// Response variables' default values are set at the beginning
		// of the first attempt.
		if ($numAttempts === 0) {
			foreach (array_keys($data) as $k) {
				if ($data[$k] instanceof ResponseVariable) {
					$data[$k]->applyDefaultValue();
				}
			}
		}
		
		$this['numAttempts'] = $this['numAttempts'] + 1;
		$this['completionStatus'] = 'incomplete';
		
		$this->setState(AssessmentItemSessionState::INTERACTING);
	}
	
	/**
	 * End the attemp by providing responses or by another action.
	 * 
	 * * If more attempts are allowed, the session remains in the 'interacting' state.
	 * * Otherwise, the 'suspended' state is activated.
	 * 
	 * @param State $responses A State composed by the responses to the item.
	 */
	public function endAttempt(State $responses = null) {
		
		$maxAttempts = $this->getItemSessionControl()->getMaxAttempts();
		if ($maxAttempts > 0 && $this['numAttempts'] >= $maxAttempts) {
			$this->endItemSession();
		}
	}
	
	/**
	 * Suspend the item session.
	 * 
	 * @param State $responseVariables A State composed of ResponseVariable objects.
	 */
	public function suspend() {
		$this->setState(AssessmentItemSessionState::SUSPENDED);
	}
	
	/**
	 * Close the item session because no more attempts are allowed. The 'completionStatus' built-in outcome variable
	 * becomes 'complete', and the state of the item session becomes 'closed'.
	 * 
	 */
	public function endItemSession() {
		$this['completionStatus'] = 'complete';
		$this->setState(AssessmentItemSessionState::CLOSED);
	}
}