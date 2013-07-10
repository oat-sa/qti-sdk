<?php

namespace qtism\data\rules;

use qtism\data\QtiComponentCollection;
use qtism\data\QtiComponent;

/**
 * The special exitTest QTI outcome rule.
 * 
 * Additional Note: This class is empty, it only exists as a 'marker'.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ExitTest extends QtiComponent implements OutcomeRule {
	
	public function getQTIClassName() {
		return 'exitTest';
	}
	
	public function __construct() {
		
	}
	
	public function getComponents() {
		return new QtiComponentCollection();
	}
}