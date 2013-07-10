<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\NumberSelected;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI numberSelected expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberSelectedMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an NumberSelected object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A NumberSelected object.
	 * @return DOMElement The corresponding numberSelected QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		return $element;
	}
	
	/**
	 * Marshall an numberSelected QTI element in its NumberSelected object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding NumberSelected object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		$object = new NumberSelected();
		$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
		$object->setIncludeCategories($baseComponent->getIncludeCategories());
		$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'numberSelected';
	}
}