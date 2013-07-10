<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\NumberResponded;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI numberResponded expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberRespondedMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an NumberResponded object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A NumberResponded object.
	 * @return DOMElement The corresponding numberResponded QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		return $element;
	}
	
	/**
	 * Marshall an numberResponded QTI element in its NumberResponded object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding NumberResponded object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		$object = new NumberResponded();
		$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
		$object->setIncludeCategories($baseComponent->getIncludeCategories());
		$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'numberResponded';
	}
}