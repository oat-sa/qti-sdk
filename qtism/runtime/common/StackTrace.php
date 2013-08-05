<?php

namespace qtism\runtime\common;

use qtism\common\collections\Stack;
use qtism\common\collections\AbstractCollection;
use \InvalidArgumentException;

/**
 * The StackTrace class is a Stack of StackTraceItem objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StackTrace extends AbstractCollection implements Stack {
	
	/**
	 * Pop a StackTraceItem object from the StackTrace.
	 * 
	 * @return StackTraceItem|null A StackTraceItem object or null if there is nothing to pop.
	 */
	public function pop() {
		$data = &$this->getDataPlaceHolder();
		$val = array_pop($data);
		return $val;
	}
	
	/**
	 * Push a given StackTraceItem object on the StackTrace.
	 * 
	 * @param StackTraceItem $value A StackTraceItem object.
	 * @throws InvalidArgumentException If $value is not a StackTraceItem object.
	 */
	public function push($value) {
		$this->checkType($value);
		$data = &$this->getDataPlaceHolder();
		array_push($data, $value);
	}
	
	public function checkType($value) {
		if (!$value instanceof StackTraceItem) {
			$msg = "The StackTrace class only accepts to store StackTraceItem objects.";
			throw new InvalidArgumentException($msg);
		}
	}
}