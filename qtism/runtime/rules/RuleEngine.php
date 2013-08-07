<?php

namespace qtism\runtime\rules;

use qtism\data\rules\Rule;
use qtism\runtime\common\State;
use qtism\data\QtiComponent;
use qtism\runtime\common\AbstractEngine;
use \InvalidArgumentException;

/**
 * The RuleEngine class provides a way to execute any Rule object on basis
 * of a given execution context.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class RuleEngine extends AbstractEngine {
	
	/**
	 * The RuleProcessorFactory object used
	 * to instantiate the right processor depending
	 * on the given Rule object to execute.
	 * 
	 * @var RuleProcessorFactory
	 */
	private $ruleProcessorFactory;
	
	/**
	 * Create a new RuleEngine object.
	 * 
	 * @param QtiComponent $rule A rule object to be executed.
	 * @param State $context An execution context if needed.
	 */
	public function __construct(QtiComponent $rule, State $context = null) {
		parent::__construct($rule, $context);
		$this->setRuleProcessorFactory(new RuleProcessorFactory());
	}
	
	/**
	 * Set the Rule object to be executed by the engine.
	 * 
	 * @param QtiComponent $rule A Rule object to be executed.
	 * @throws InvalidArgumentException If $rule is not a Rule object.
	 */
	public function setComponent(QtiComponent $rule) {
		if ($rule instanceof Rule) {
			parent::setComponent($rule);
		}
		else {
			$msg = "The RuleEngine class only accepts to execute Rule objects.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Set the RuleProcessorFactory to be used.
	 * 
	 * @param RuleProcessorFactory $ruleProcessorFactory A RuleProcessorFactory object.
	 */
	protected function setRuleProcessorFactory(RuleProcessorFactory $ruleProcessorFactory) {
		$this->ruleProcessorFactory = $ruleProcessorFactory;
	}
	
	/**
	 * Get the RuleProcessorFactory to be used.
	 * 
	 * @return RuleProcessorFactory A RuleProcessorFactory object.
	 */
	protected function getRuleProcessorFactory() {
		return $this->ruleProcessorFactory;
	}
	
	/**
	 * Execute the current Rule object.
	 * 
	 * @throws RuleProcessingException
	 */
	public function process() {
		$rule = $this->getComponent();
		$context = $this->getContext();
		
		$processor = $this->getRuleProcessorFactory()->createProcessor($rule);
		$processor->setState($context);
		$processor->process();
		
		$this->trace($rule->getQtiClassName());
	}
}