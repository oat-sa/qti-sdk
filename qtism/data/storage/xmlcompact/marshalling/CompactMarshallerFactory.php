<?php

namespace qtism\data\storage\xmlcompact\marshalling;

use qtism\data\storage\xml\marshalling\MarshallerFactory;

class CompactMarshallerFactory extends MarshallerFactory {
	
	public function __construct() {
		parent::__construct();
		
		$this->addMappingEntry('assessmentItemRef', 'qtism\\data\\storage\\xmlcompact\\marshalling\\CompactAssessmentItemRefMarshaller');
	}
}