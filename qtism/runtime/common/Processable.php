<?php

namespace qtism\runtime\common;

/**
 * Any "processable" class must implement this interface.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface Processable {
	
	/**
	 * Trigger the processing of the Processable object.
	 */
	public function process();
}