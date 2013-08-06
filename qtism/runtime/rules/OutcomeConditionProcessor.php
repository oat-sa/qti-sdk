<?php
namespace qtism\runtime\rules;

use qtism\runtime\expressions\ExpressionEngine;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\OutcomeCondition;
use qtism\data\rules\Rule;
use \InvalidArgumentException;

/**
 * From IMS QTI:
 * 
 * If the expression given in the outcomeIf or outcomeElseIf evaluates to true then 
 * the sub-rules contained within it are followed and any following outcomeElseIf or 
 * outcomeElse parts are ignored for this outcome condition.
 * 
 * If the expression given in the outcomeIf or outcomeElseIf does not evaluate to true 
 * then consideration passes to the next outcomeElseIf or, if there are no more 
 * outcomeElseIf parts then the sub-rules of the outcomeElse are followed 
 * (if specified).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeConditionProcessor extends RuleProcessor {
	
	/**
	 * The trail stack, an array of Rule objects.
	 * 
	 * @var array
	 */
	private $trail = array();
	
	/**
	 * The RuleProcessorFactory object used to create
	 * appropriate rule processors.
	 * 
	 * @var RuleProcessorFactory
	 */
	private $ruleProcessorFactory;
	
	/**
	 * Create a new OutcomeConditionProcessor.
	 * 
	 * @param Rule $rule An OutcomeCondition rule object.
	 * @throws InvalidArgumentException If $rule is not an OutcomeCondition object.
	 */
	public function __construct(Rule $rule) {
		parent::__construct($rule);
		$this->setRuleProcessorFactory(new RuleProcessorFactory());
	}
	
	/**
	 * Set the OutcomeCondition object to be processed.
	 * 
	 * @param Rule $rule An OutcomeCondition object.
	 * @throws InvalidArgumentException If $rule is not an OutcomeCondition object.
	 */
	public function setRule(Rule $rule) {
		if ($rule instanceof OutcomeCondition) {
			parent::setRule($rule);
		}
		else {
			$msg = "The OutcomeConditionProcessor only accepts OutcomeCondition objects to be processed.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Set the trail stack.
	 * 
	 * @param array $trail An array of trailed QtiComponent objects.
	 */
	public function setTrail(array &$trail) {
		$this->trail = $trail;
	}
	
	/**
	 * Get the trail stack.
	 * 
	 * @return array An array of trailed Rule objects.
	 */
	public function &getTrail() {
		return $this->trail;
	}
	
	/**
	 * Push some Rule objects on the trail stack.
	 * 
	 * @param QtiComponentCollection|QtiComponent $components A collection of Rule objects.
	 */
	public function pushTrail($components) {
		$i = count($components);
		$trail = &$this->getTrail();
		
		if ($components instanceof QtiComponent) {
			array_push($trail, $components);
		}
		
		while ($i >= 1) {
			$i--;
			array_push($trail, $components[$i]);
		}
	}
	
	/**
	 * Pop a Rule object from the trail.
	 * 
	 * @return QtiComponent A Rule object.
	 */
	public function popTrail() {
		$trail = &$this->getTrail();
		return array_pop($trail);
	}
	
	/**
	 * Set the RuleProcessorFactory object used to create appropriate rule processors.
	 * 
	 * @param RuleProcessorFactory $ruleProcessorFactory A RuleProcessorFactory object.
	 */
	public function setRuleProcessorFactory(RuleProcessorFactory $ruleProcessorFactory) {
		$this->ruleProcessorFactory = $ruleProcessorFactory;
	}
	
	/**
	 * Get the RuleProcessorFactory object used to create appropriate rule processors.
	 * 
	 * @return RuleProcessorFactory A RuleProcessorFactory object.
	 */
	public function getRuleProcessorFactory() {
		return $this->ruleProcessorFactory;
	}
	
	/**
	 * Process the OutcomeCondition according to the current state.
	 * 
	 * @throws RuleProcessingException
	 */
	public function process() {
		
		$state = $this->getState();
		$this->pushTrail(new QtiComponentCollection(array($this->getRule())));
		
		while (count($this->getTrail()) > 0) {

			$rule = $this->popTrail();
			
			if ($rule instanceof OutcomeCondition) {
				
				// Let's try for if.
				$outcomeIf = $rule->getOutcomeIf();
				$ifExpression = $outcomeIf->getExpression();
				$exprEngine = new ExpressionEngine($ifExpression, $state);
				
				if ($exprEngine->process() === true) {
					// Follow the if.
					$this->pushTrail($outcomeIf->getOutcomeRules());
				}
				else {
					// Let's try for else ifs.
					$followElseIf = false;
					
					foreach ($rule->getOutcomeElseIfs() as $outcomeElseIf) {
						$elseIfExpression = $outcomeElseIf->getExpression();
						$exprEngine->setComponent($elseIfExpression);
						
						if ($exprEngine->process() === true) {
							// Follow the current else if.
							$this->pushTrail($outcomeElseIf->getOutcomeRules());
							$followElseIf = true;
							break;
						}
					}
					
					if ($followElseIf === false) {
						// No else if followed, the last resort is the else.
						$this->pushTrail($rule->getOutcomeElse()->getOutcomeRules());
					}
				}
			}
			else {
				// $rule is another Rule than OutcomeCondition.
				$processor = $this->getRuleProcessorFactory()->createProcessor($rule);
				$processor->setState($state);
				$processor->process();
			}
		}
	}
}