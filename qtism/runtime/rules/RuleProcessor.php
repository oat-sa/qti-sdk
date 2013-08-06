<?php

namespace qtism\runtime\rules;

use qtism\runtime\common\State;
use qtism\data\rules\Rule;
use qtism\runtime\common\Processable;

/**
 * The RuleProcessor class aims at processing QTI Data Model Rule objects which are:
 * 
 * * responseCondition
 * * outcomeCondition
 * * setOutcomeValue
 * * lookupOutcomeValue
 * * branchRule
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class RuleProcessor implements Processable {
	
	/**
	 * The Rule object to be processed.
	 * 
	 * @var Rule
	 */
	private $rule;
	
	/**
	 * The State object.
	 * 
	 * @var State
	 */
	private $state;
	
	/**
	 * Create a new RuleProcessor object aiming at processing the $rule Rule object.
	 * 
	 * @param Rule $rule A Rule object to be processed by the processor.
	 */
	public function __construct(Rule $rule) {
		$this->setRule($rule);
		$this->setState(new State());
	}
	
	/**
	 * Set the QTI Data Model Rule object to be processed by the 
	 * 
	 * @param Rule $rule
	 */
	public function setRule(Rule $rule) {
		$this->rule = $rule;
	}
	
	public function getRule() {
		return $this->rule;
	}
	
	/**
	 * Set the current State object.
	 *
	 * @param State $state A State object.
	 */
	public function setState(State $state) {
		$this->state = $state;
	}
	
	/**
	 * Get the current State object.
	 *
	 * @return State
	 */
	public function getState() {
		return $this->state;
	}
	
	/**
	 * Process the Rule object to be processed.
	 * 
	 * @return mixed A QTI Runtime compliant value or void if nothing to return.
	 * @throws RuleProcessingException If an error occurs while processing the Rule object.
	 */
	public abstract function process();
	
}