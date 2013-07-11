<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;

use qtism\data\QtiComponent;

/**
 * From IMS QTI:
 * 
 * If the expression given in the outcomeIf or outcomeElseIf evaluates to true then 
 * the sub-rules contained within it are followed and any following outcomeElseIf 
 * or outcomeElse parts are ignored for this outcome condition.
 * 
 * If the expression given in the outcomeIf or outcomeElseIf does not evaluate 
 * to true then consideration passes to the next outcomeElseIf or, if there are 
 * no more outcomeElseIf parts then the sub-rules of the outcomeElse are 
 * followed (if specified).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class OutcomeCondition extends QtiComponent implements OutcomeRule {
	
	/**
	 * An OutcomeIf object.
	 * 
	 * @var OutcomeIf
	 */
	private $outcomeIf;
	
	/**
	 * A collection of OutcomeElseIf objects.
	 * 
	 * @var OutcomeElseIfCollection
	 */
	private $outcomeElseIfs;
	
	/**
	 * An optional OutcomeElse object.
	 * 
	 * @var OutcomeElse
	 */
	private $outcomeElse = null;
	
	/**
	 * Create a new instance of OutcomeCondition.
	 * 
	 * @param OutcomeIf $outcomeIf An OutcomeIf object.
	 * @param OutcomeElseIfCollection $outcomeElseIfs A collection of OutcomeElseIf objects.
	 * @param OutcomeElse $outcomeElse An OutcomeElse object.
	 */
	public function __construct(OutcomeIf $outcomeIf, OutcomeElseIfCollection $outcomeElseIfs = null, OutcomeElse $outcomeElse = null) {
		$this->setOutcomeIf($outcomeIf);
		$this->setOutcomeElse($outcomeElse);
		$this->setOutcomeElseIfs((is_null($outcomeElseIfs)) ? new OutcomeElseIfCollection() : $outcomeElseIfs);
	}
	
	/**
	 * Get the OutcomeIf object.
	 * 
	 * @return OutcomeIf An OutcomeIf object.
	 */
	public function getOutcomeIf() {
		return $this->outcomeIf;
	}
	
	/**
	 * Set the OutcomeIf object.
	 * 
	 * @param OutcomeIf $outcomeIf An OutcomeIf object.
	 */
	public function setOutcomeIf(OutcomeIf $outcomeIf) {
		$this->outcomeIf = $outcomeIf;
	}
	
	/**
	 * Get the collection of OutcomeElseIf objects.
	 * 
	 * @return OutcomeElseIfCollection An OutcomeElseIfCollection object.
	 */
	public function getOutcomeElseIfs() {
		return $this->outcomeElseIfs;
	}
	
	/**
	 * Set the collection of OutcomeElseIf objects.
	 * 
	 * @param OutcomeElseIfCollection $outcomeElseIfs An OutcomeElseIfCollection object.
	 */
	public function setOutcomeElseIfs(OutcomeElseIfCollection $outcomeElseIfs) {
		$this->outcomeElseIfs = $outcomeElseIfs;
	}
	
	/**
	 * Get the optional OutcomeElse object. Returns null if not specified.
	 * 
	 * @return OutcomeElse An OutcomeElse object.
	 */
	public function getOutcomeElse() {
		return $this->outcomeElse;
	}
	
	/**
	 * Set the optional OutcomeElse object. A null value means there is no else.
	 * 
	 * @param OutcomeElse $outcomeElse An OutcomeElse object.
	 */
	public function setOutcomeElse(OutcomeElse $outcomeElse = null) {
		$this->outcomeElse = $outcomeElse;
	}
	
	public function getQtiClassName() {
		return 'outcomeCondition';
	}
	
	public function getComponents() {
		$comp = array_merge(array($this->getOutcomeIf()),
							$this->getOutcomeElseIfs()->getArrayCopy());
		
		if (!is_null($this->getOutcomeElse())) {
			$comp[] = $this->getOutcomeElse();
		}
		
		return new QtiComponentCollection($comp);
	}
}