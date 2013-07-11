<?php

namespace qtism\data;

use \InvalidArgumentException;

/**
 * In the context of a specific assessmentTest an item, or group of items, 
 * may be subject to a time constraint. This specification supports both 
 * minimum and maximum time constraints. The controlled time for a single 
 * item is simply the duration of the item session as defined by the builtin 
 * response variable duration. For assessmentSections, testParts and whole 
 * assessmentTests the time limits relate to the durations of all the item 
 * sessions plus any other time spent navigating that part of the test. 
 * In other words, the time includes time spent in states where no item 
 * is being interacted with, such as dedicated navigation screens.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TimeLimits extends QtiComponent {
	
	/**
	 * Minimum time.
	 * 
	 * From IMS QTI:
	 * 
	 * Minimum times are applicable to assessmentSections and assessmentItems only when
	 * linear navigation mode is in effect.
	 * 
	 * false = unlimited
	 * 
	 * @var integer|boolean
	 */
	private $minTime = false;
	
	/**
	 * Maximum time.
	 * 
	 * false = unlimited
	 * 
	 * @var integer|boolean
	 */
	private $maxTime = false;
	
	/**
	 * From IMS QTI:
	 * 
	 * The allowLateSubmission attribute regulates whether a candidate's response that is 
	 * beyond the maxTime should still be accepted.
	 * 
	 * @var boolean
	 */
	private $allowLateSubmission = false;
	
	/**
	 * Create a new instance of TimeLimits.
	 * 
	 * @param integer|boolean $minTime The minimum time. Give false if not defined.
	 * @param integer|boolan $maxTime The maximum time. Give false if not defined.
	 * @param string $allowLateSubmission Wether it allows late submission of responses.
	 */
	public function __construct($minTime = false, $maxTime = false, $allowLateSubmission = false) {
		$this->setMinTime($minTime);
		$this->setMaxTime($maxTime);
		$this->setAllowLateSubmission($allowLateSubmission);
	}
	
	/**
	 * Get the minimum time.
	 * 
	 * @return int A duration in seconds.
	 */
	public function getMinTime() {
		return $this->minTime;
	}
	
	/**
	 * Whether a minTime is defined.
	 * 
	 * @return boolean
	 */
	public function hasMinTime() {
		return $this->getMinTime() !== false;
	}
	
	/**
	 * Set the minimum time.
	 * 
	 * @param integer|boolean $minTime A duration in seconds or (boolean) false if not specified.
	 * @throws InvalidArgumentException If $minTime is not an integer nor (boolean) false.
	 */
	public function setMinTime($minTime) {
		if (is_int($minTime) || (is_bool($minTime) && $minTime === false)) {
			$this->minTime = $minTime;
		}
		else {
			$msg = "MinTime must be an integer or (boolean) false, '" . gettype($minTime) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the maximum time. Returns empty string if unlimited.
	 * 
	 * @return string A duration.
	 */
	public function getMaxTime() {
		return $this->maxTime;
	}
	
	/**
	 * Whether a maxTime is defined.
	 * 
	 * @return boolean
	 */
	public function hasMaxTime() {
		return $this->getMaxTime() !== false;
	}
	
	/**
	 * Set the maximum time in seconds. Set to (boolean) false if unlimited.
	 * 
	 * @param integer|boolean $maxTime A duration in seconds or (boolean) false if not specified.
	 * @throws InvalidArgumentException If $maxTime is not a string.
	 */
	public function setMaxTime($maxTime) {
		if (is_int($maxTime) || (is_bool($maxTime) && $maxTime === false)) {
			$this->maxTime = $maxTime;
		}
		else {
			$msg = "MaxTime must be an integer or (boolean) false, '" . gettype($maxTime) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Wether a candidate's response that is beyond the maxTime should be still
	 * accepted.
	 * 
	 * @return boolean true if the candidate's response should still be accepted, false if not.
	 */
	public function doesAllowLateSubmission() {
		return $this->allowLateSubmission;
	}
	
	/**
	 * Set wether a candidate's response that is beyond the maxTime should be still
	 * accepted.
	 * 
	 * @param boolean $allowLateSubmission true if the candidate's response should still be accepted, false if not.
	 * @throws InvalidArgumentException If $allowLateSubmission is not a boolean.
	 */
	public function setAllowLateSubmission($allowLateSubmission) {
		if (is_bool($allowLateSubmission)) {
			$this->allowLateSubmission = $allowLateSubmission;
		}
		else {
			$msg = "AllowLateSubmission must be a boolean, '" . gettype($allowLateSubmission) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	public function getQtiClassName() {
		return 'timeLimits';
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}