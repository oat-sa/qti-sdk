<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;

use qtism\data\QtiComponent;
use \InvalidArgumentException;

/**
 * The ResponseElse class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ResponseElse extends QtiComponent {
	
	/**
	 * A collection of ResponseRule objects to be evaluated.
	 * 
	 * @var ResponseRuleCollection
	 */
	private $responseRules;
	
	/**
	 * Create a new instance of ResponseElse.
	 * 
	 * @param ResponseRuleCollection $responseRules A collection of ResponseRule objects.
	 * @throws InvalidArgumentException If $responseRules is an empty collection.
	 */
	public function __construct(ResponseRuleCollection $responseRules) {
		$this->responseRules = $responseRules;
	}
	
	/**
	 * Get the ResponseRule objects to be evaluated.
	 * 
	 * @return ResponseRuleCollection A collection of ResponseRule objects.
	 */
	public function getResponseRules() {
		return $this->responseRules;
	}
	
	/**
	 * Set the ResponseRule objects to be evaluated.
	 * 
	 * @param ResponseRuleCollection $responseRules A collection of ResponseRule objects.
	 * @throws InvalidArgumentException If $responseRules is an empty collection.
	 */
	public function setOutcomeRules(ResponseRuleCollection $responseRules) {
		if (count($responseRules) > 0) {
			$this->responseRules = $responseRules;
		}
		else {
			$msg = "A ResponseElse object must be bound to at least one ResponseRule object.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'responseElse';
	}
	
	public function getComponents() {
		$comp = $this->getResponseRules()->getArrayCopy();
		return new QtiComponentCollection($comp);
	}
}