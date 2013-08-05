<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;

/**
 * The special exitResponse QTI response rule.
 * 
 * From IMS QTI:
 * 
 * The exit response rule terminates response processing immediately (for this invocation).
 * 
 * Additional Note: This class is empty, it only exists as a 'marker'.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitResponse extends QtiComponent implements ResponseRule {
	
	public function getQtiClassName() {
		return 'exitResponse';
	}
	
	/**
	 * Create a new ExitResponse object.
	 * 
	 */
	public function __construct() {
		
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}