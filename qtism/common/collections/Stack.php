<?php

namespace qtism\common\collections;

/**
 * Collections that are implemented as a stack must
 * implement this interface.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface Stack {
	
	/**
	 * Push a value on the Stack.
	 * 
	 * @param mixed $value A value.
	 */
	public function push($value);
	
	/**
	 * Pop a value from the Stack.
	 * 
	 * @return mixed A value.
	 */
	public function pop();
}