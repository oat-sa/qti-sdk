<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;

use qtism\data\QtiComponent;
use \InvalidArgumentException;

/**
 * The OutcomeElse class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeElse extends QtiComponent {
	
	/**
	 * A collection of OutcomeRule objects to be evaluated.
	 * 
	 * @var OutcomeRuleCollection
	 */
	private $outcomeRules;
	
	/**
	 * Create a new instance of OutcomeElse.
	 * 
	 * @param OutcomeRuleCollection $outcomeRules A collection of OutcomeRule objects.
	 * @throws InvalidArgumentException If $outcomeRules is an empty collection.
	 */
	public function __construct(OutcomeRuleCollection $outcomeRules) {
		$this->outcomeRules = $outcomeRules;
	}
	
	/**
	 * Get the OutcomeRule objects to be evaluated.
	 * 
	 * @return OutcomeRuleCollection A collection of OutcomeRule objects.
	 */
	public function getOutcomeRules() {
		return $this->outcomeRules;
	}
	
	/**
	 * Set the OutcomeRule objects to be evaluated.
	 * 
	 * @param OutcomeRuleCollection $outcomeRules A collection of OutcomeRule objects.
	 * @throws InvalidArgumentException If $outcomeRules is an empty collection.
	 */
	public function setOutcomeRules(OutcomeRuleCollection $outcomeRules) {
		if (count($outcomeRules) > 0) {
			$this->outcomeRules = $outcomeRules;
		}
		else {
			$msg = "An OutcomeElse object must be bound to at least one OutcomeRule object.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'outcomeElse';
	}
	
	public function getComponents() {
		$comp = $this->getOutcomeRules()->getArrayCopy();
		return new QtiComponentCollection($comp);
	}
}