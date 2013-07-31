<?php

namespace qtism\common\collections;

use qtism\common\Comparable;
use \InvalidArgumentException;
use \UnexpectedValueException;

/**
 * The AbstractCollection class is the base class of all collections.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractCollection implements \Countable, \Iterator, \ArrayAccess {
	
	/**
	 * A data place holder to actually store data for the collection.
	 * 
	 * @var array
	 */
	protected $dataPlaceHolder = array();
	
	/**
	 * Create a new instance of AbstractCollection.
	 * 
	 * @param array $array An array of data to be stored by the collection.
	 * @throws \InvalidArgumentException If the $array argument contains invalid datatypes, checked with the AbstractCollection::checkType() method.
	 */
	public function __construct(array $array = array()) {
		
		$dataPlaceHolder = &$this->getDataPlaceHolder();
		
		foreach ($array as $k => $a) {
			$this->checkType($a);
			$dataPlaceHolder[$k] = $a;
		}
		
		reset($dataPlaceHolder);
	}
	
	/**
	 * Set the data placeholder.
	 * 
	 * @param array $dataPlaceHolder An array.
	 */
	protected function setDataPlaceHolder(array &$dataPlaceHolder) {
		$this->dataPlaceHolder = $dataPlaceHolder;
	}
	
	/**
	 * Get the data placeholder.
	 * 
	 * @return array The data placeholder of the collection.
	 */
	protected function &getDataPlaceHolder() {
		return $this->dataPlaceHolder;
	}
	
	/**
	 * Check the type of a given $value that has to be stored by the collection.
	 * 
	 * @param mixed $value A given value.
	 * @throws \InvalidArgumentException If the datatype of $value is incorrect.
	 */
	abstract protected function checkType($value);
	
	/**
	 * Return the amout of values currently stored by the collection.
	 * 
	 * @return int The amount of values stored by the collection.
	 */
	public function count() {
		$placeholder = &$this->getDataPlaceHolder();
		return count($placeholder);
	}
	
	/**
	 * Return the current element of the collection while iterating.
	 * 
	 * @return mixed The current element.
	 */
	public function current() {
		// @todo find why sometimes, the current value is the placeholder itself!
		$placeholder = &$this->getDataPlaceHolder();
		return current($placeholder);
	}
	
	/**
	 * Move forward to the next element of the collection while iterating.
	 */
	public function next() {
		$placeholder = &$this->getDataPlaceHolder();
		next($placeholder);
	}
	
	/**
	 * Return the key of the current element of the collection while iterating.
	 * 
	 * @return mixed Depends on the implementation.
	 */
	public function key() {
		$placeholder = &$this->getDataPlaceHolder();
		return key($placeholder);
	}
	
	/**
	 * Check if the current position of the iterator is valid.
	 * 
	 * @return boolean true on success or false on failure.
	 */
	public function valid() {
		$placeholder = &$this->getDataPlaceHolder(); 
		return key($placeholder) !== null;
	}
	
	/**
	 * Rewind the iterator to the first element of the collection;
	 */
	public function rewind() {
		$placeholder = &$this->getDataPlaceHolder();
		reset($placeholder);
	}
	
	/**
	 * Wether a offset exists.
	 * 
	 * @param mixed $offset An offset to check for.
	 * @return Wether the offset exist.
	 */
	public function offsetExists($offset) {
		$placeholder = &$this->getDataPlaceHolder();
		return isset($placeholder[$offset]);
	}
	
	/**
	 * Offset to retrieve. If the $offset does not reference any value, a null
	 * value is returned.
	 * 
	 * @param mixed $offset The offset to retrieve.
	 * @return mixex The value at specified offset.
	 */
	public function offsetGet($offset) {
		$placeholder = &$this->getDataPlaceHolder();
		return isset($placeholder[$offset]) ? $placeholder[$offset] : null;
	}
	
	/**
	 * Offset to set. If a value is already set for the given $offset, its value
	 * will be overriden with $value.
	 * 
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 * @throws InvalidArgumentException If $value has not a valid type regarding the implementation.
	 */
	public function offsetSet($offset, $value) {
		$this->checkType($value);
		
		$placeholder = &$this->getDataPlaceHolder();
		if (is_null($offset)) {
			$placeholder[] = $value;
		}
		else {
			$placeholder[$offset] = $value;
		}
	}
	
	public function offsetUnset($offset) {
		$placeholder = &$this->getDataPlaceHolder();
		unset($placeholder[$offset]);
	}
	
	/**
	 * Get a copy of the collection as an array. This method is implemented in order
	 * to implement ArrayObject in a near future.
	 * 
	 * @return array The collection as an array of data.
	 */
	public function getArrayCopy() {
		return $this->dataPlaceHolder;
	}
	
	/**
	 * Whether the collection contains a given $value. The comparison 
	 * is strict, using the === operator.
	 * 
	 * @param mixed $value A value.
	 * @return boolean Whether the collection contains $value.
	 */
	public function contains($value) {
		foreach (array_keys($this->getDataPlaceHolder()) as $key) {
			$data = $this[$key];
			if ($value === $data || ($data instanceof Comparable && $data->equals($value))) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Attach a given $object to the collection.
	 * 
	 * @param mixed $object An object.
	 * @throws InvalidArgumentException If $object is not an 'object' type or not compliant with the typing of the collection.
	 */
	public function attach($object) {
		$this->checkType($object);
		
		if (gettype($object) !== 'object') {
			$msg = "You can only attach 'objects' into an AbstractCollection, '" . gettype($object) . "' given";
			throw new InvalidArgumentException($msg);
		}
		else if (!$this->contains($object)) {
			$data = &$this->getDataPlaceHolder();
			$data[] = $object;
		}
	}
	
	/**
	 * Detach a given $object from the collection.
	 * 
	 * @param mixed $object An object.
	 * @throws InvalidArgumentException If $object is not an 'object' type or not compliant with the typing of the collection.
	 * @throws UnexpectedValueException If $object cannot be found in the collection.
	 */
	public function detach($object) {
		$this->checkType($object);
		
		if (gettype($object) !== 'object') {
			$msg = "You can only attach 'objects' into an AbstractCollection, '" . gettype($object) . "' given.";
			throw new InvalidArgumentException($msg);
		}
		
		$data = &$this->getDataPlaceHolder();
		for ($i = 0; $i < count($data); $i++) {
			
			if ($data[$i] === $object) {
				unset($data[$i]);
				
				// Reset keys.
				$data = array_values($data);
				$this->setDataPlaceHolder($data);
				return;
			}
		}
		
		$msg = "The object you want to detach could not be found in the collection.";
		throw new UnexpectedValueException($msg);
	}
	
	/**
	 * Replace an $object in the collection by another $replacement $object.
	 * 
	 * @param mixed $object An object to be replaced.
	 * @param mixed $replacement An object to be used as a replacement.
	 * @throws InvalidArgumentException If $object or $replacement are not compliant with the current collection typing.
	 * @throws UnexpectedValueException If $object is not contained in the collection.
	 */
	public function replace($object, $replacement) {
		$this->checkType($object);
		$this->checkType($replacement);
		
		if (gettype($object) !== 'object') {
			$msg = "You can only attach 'objects' into an AbstractCollection, '" . gettype($object) . "' given.";
			throw new InvalidArgumentException($msg);
		}
		
		if (gettype($replacement) !== 'object') {
			$msg = "You can only attach 'objects' into an AbstractCollection, '" . gettype($replacement) . "' given.";
			throw new InvalidArgumentException($msg);
		}
		
		$data = &$this->getDataPlaceHolder();
		for ($i = 0; $i < count($data); $i++) {
			if ($data[$i] === $object) {
				$data[$i] = $replacement;
				return;
			}
		}
		
		$msg = "The object you want to replace could not be found.";
		throw new UnexpectedValueException($msg);
	}
	
	/**
	 * Reset the collection to an empty one.
	 */
	public function reset() {
		$a = array();
		$this->setDataPlaceHolder($a);
	}
	
	public function __clone() {
		foreach ($this->getDataPlaceHolder() as $key => $value) {
			if (gettype($value) === 'object') {
				$this[$key] = clone $value;
			}
		}
	}
}