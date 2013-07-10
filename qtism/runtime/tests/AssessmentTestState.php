<?php

namespace qtism\runtime\tests;

use qtism\runtime\common\State;
use qtism\data\state\WeightCollection;

/**
 * The State of an AssessmentTest Instance.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestState extends State {
	
	/**
	 * A Collection of QTI Data Model Weight objects.
	 * 
	 * @var WeightCollection
	 */
	private $weights;
	
	/**
	 * Create a new AssessmentTestState object.
	 *
	 * @param array $array An optional array of Variable objects.
	 * @param WeightCollection A collection of QTI Data Model Weight objects.
	 * @throws InvalidArgumentException If an object of $array is not a Variable object.
	 */
	public function __construct(array $array = array(), WeightCollection $weights = null) {
		parent::__construct($array);
		$this->setWeights((empty($weights)) ? new WeightCollection() : $weights);
	}
	
	/**
	 * Set the Weights involved in the AssessmentTest State.
	 * 
	 * @param WeightCollection $weights A collection of QTI Data Model Weight objects.
	 */
	public function setWeights(WeightCollection $weights) {
		$this->weights = $weights;
	}
	
	/**
	 * Get the Weights involved in the AssessmentTest State.
	 * 
	 * @return WeightCollection A collection of QTI Data Model Weight objects.
	 */
	public function getWeights() {
		return $this->weights;
	}
}