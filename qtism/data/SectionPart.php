<?php

namespace qtism\data;

use qtism\data\rules\PreConditionCollection;
use qtism\data\rules\BranchRuleCollection;
use qtism\common\utils\Format;
use \InvalidArgumentException;

class SectionPart extends QtiComponent {
	
	/**
	 * From IMS QTI:
	 * 
	 * The identifier of the section or item reference must be unique within the test and 
	 * must not be the identifier of any testPart.
	 * 
	 * @var string
	 */
	private $identifier;
	
	/**
	 * From IMS QTI:
	 * 
	 * If a child element is required it must appear (at least once) in the selection.
	 * It is in error if a section contains a selection rule that selects fewer child
	 * elements than the number of required elements it contains.
	 * 
	 * @var boolean
	 */
	private $required = false;
	
	/**
	 * From IMS QTI:
	 * 
	 * If a child element is required it must appear (at least once) in the selection. 
	 * It is in error if a section contains a selection rule that selects fewer child 
	 * elements than the number of required elements it contains.
	 * 
	 * @var boolean
	 */
	private $fixed = false;
	
	/**
	 * From IMS QTI:
	 * 
	 * An optional set of conditions evaluated during the test, that determine if the 
	 * item or section is to be skipped (in nonlinear mode, pre-conditions are ignored).
	 * 
	 * @var PreConditionCollection
	 */
	private $preConditions;
	
	/**
	 * From IMS QTI:
	 * 
	 * An optional set of rules, evaluated during the test, for setting an alternative 
	 * target as the next item or section (in nonlinear mode, branch rules are ignored).
	 * 
	 * @var BranchRuleCollection
	 */
	private $branchRules;
	
	/**
	 * From IMS QTI:
	 * 
	 * Parameters used to control the allowable states of each item session 
	 * (may be overridden at sub-section or item level).
	 * 
	 * @var ItemSessionControl
	 */
	private $itemSessionControl = null;
	
	/**
	 * From IMS QTI:
	 * 
	 * Optionally controls the amount of time a candidate is allowed for this item or section.
	 * 
	 * @var TimeLimits
	 */
	private $timeLimits = null;
	
	/**
	 * Create a new instance of SectionPart.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @param boolean $required true if it must absolutely appear during the session, false if not.
	 * @param boolean $fixed true if it must not be affected by shuffling, false if it can be affected by shuffling.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier, $required or $fixed are not booleans.
	 */
	public function __construct($identifier, $required = false, $fixed = false) {
		$this->setIdentifier($identifier);
		$this->setRequired($required);
		$this->setFixed($fixed);
		$this->setPreConditions(new PreConditionCollection());
		$this->setBranchRules(new BranchRuleCollection());
	}
	
	/**
	 * Get the identifier of the Section Part.
	 * 
	 * @return string A QTI Identifier.
	 */
	public function getIdentifier() {
		return $this->identifier;
	}
	
	/**
	 * Set the identifier of the Section Part.
	 * 
	 * @param string $identifier A QTI Identifier.
	 * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier.
	 */
	public function setIdentifier($identifier) {
		if (Format::isIdentifier($identifier)) {
			$this->identifier = $identifier;
		}
		else {
			$msg = "'{identifier}' is not a valid QTI Identifier.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Must appear at least once?
	 * 
	 * @return boolean true if must appear at least one, false if not.
	 */
	public function isRequired() {
		return $this->required;
	}
	
	/**
	 * Set if it must appear at least one during the session.
	 * 
	 * @param boolean $required true if it must appear at least one, otherwise false.
	 * @throws InvalidArgumentException If $required is not a boolean.
	 */
	public function setRequired($required) {
		if (is_bool($required)) {
			$this->required = $required;
		}
		else {
			$msg = "Required must be a boolean, '" . gettype($required) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Subject to shuffling?
	 * 
	 * @return boolean true if subject to shuffling, false if not.
	 */
	public function isFixed() {
		return $this->fixed;
	}
	
	/**
	 * Set if the section part is subject to shuffling.
	 * 
	 * @param boolean $fixed true if subject to shuffling, false if not.
	 * @throws InvalidArgumentException If $fixed is not a boolean.
	 */
	public function setFixed($fixed) {
		if (is_bool($fixed)) {
			$this->fixed = $fixed;
		}
		else {
			$msg = "Fixed must be a boolean, '" . gettype($fixed) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the collection of PreConditions bound to this section part.
	 * 
	 * @return PreConditionCollection A collection of PreCondition objects.
	 */
	public function getPreConditions() {
		return $this->preConditions;
	}
	
	/**
	 * Set the collection of PreConditions bound to this sections part.
	 * 
	 * @param PreConditionCollection $preConditions A collection of PreCondition objects.
	 */
	public function setPreConditions(PreConditionCollection $preConditions) {
		$this->preConditions = $preConditions;
	}
	
	/**
	 * Get the collection of BranchRules bound to this section part.
	 * 
	 * @return BranchRuleCollection A collection of BranchRule objects.
	 */
	public function getBranchRules() {
		return $this->branchRules;
	}
	
	/**
	 * Set the collection of BranchRules bound to this section part.
	 * 
	 * @param BranchRuleCollection $branchRules A collection of BranchRule objects.
	 */
	public function setBranchRules(BranchRuleCollection $branchRules) {
		$this->branchRules = $branchRules;
	}
	
	/**
	 * Get the parameters used to control the allowable states of each item session.
	 * Returns null value if not specified.
	 * 
	 * @return ItemSessionControl
	 */
	public function getItemSessionControl() {
		return $this->itemSessionControl;
	}
	
	/**
	 * Set the parameters used to control the allowable states of each item session.
	 * 
	 * @param ItemSessionControl $itemSessionControl An ItemSessionControl object.
	 */
	public function setItemSessionControl(ItemSessionControl $itemSessionControl = null) {
		$this->itemSessionControl = $itemSessionControl;
	}
	
	/**
	 * Set the amount of time a candidate is allowed for this section.
	 * Returns null value if not specified.
	 * 
	 * @return TimeLimits A TimeLimits object.
	 */
	public function getTimeLimits() {
		return $this->timeLimits;
	}
	
	/**
	 * Set the amount of time a candidate is allowed for this section.
	 * Returns null value if not specified.
	 * 
	 * @param TimeLimits $timeLimits A TimeLimits object.
	 */
	public function setTimeLimits(TimeLimits $timeLimits = null) {
		$this->timeLimits = $timeLimits;
	}
	
	public function getQTIClassName() {
		return 'sectionPart';
	}
	
	public function getComponents() {
		$comp = array_merge($this->getBranchRules()->getArrayCopy(),
							$this->getPreConditions()->getArrayCopy());
		
		if ($this->getTimeLimits() !== null) {
			$comp[] = $this->getTimeLimits();
		}
		
		if ($this->getItemSessionControl() !== null) {
			$comp[] = $this->getItemSessionControl();
		}
		
		return new QtiComponentCollection($comp);
	}
}