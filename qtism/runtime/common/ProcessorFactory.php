<?php

namespace qtism\runtime\common;

use qtism\data\QtiComponent;

/**
 * The ProcessorFactory must be implemented by any factory class that produces
 * Processable objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface ProcessorFactory {
	
	/**
	 * Create a Processable object able to process $component.
	 * 
	 * @param QtiComponent $component A QtiComponent object that the returned Processable object is able to process.
	 * @return Processable A Processable object able to process $component.
	 */
	public function createProcessor(QtiComponent $component);
}