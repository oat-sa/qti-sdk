<?php

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\expressions\NumberIncorrect;
use \DOMElement;

/**
 * A marshalling/unmarshalling implementation for the QTI numberIncorrect expression.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class NumberIncorrectMarshaller extends ItemSubsetMarshaller {
	
	/**
	 * Marshall an NumberIncorrect object in its DOMElement equivalent.
	 * 
	 * @param QtiComponent A NumberIncorrect object.
	 * @return DOMElement The corresponding numberIncorrect QTI element.
	 */
	protected function marshall(QtiComponent $component) {
		$element = parent::marshall($component);
		return $element;
	}
	
	/**
	 * Marshall an numberIncorrect QTI element in its NumberIncorrect object equivalent.
	 * 
	 * @param DOMElement A DOMElement object.
	 * @return QtiComponent The corresponding NumberIncorrect object.
	 */
	protected function unmarshall(DOMElement $element) {
		$baseComponent = parent::unmarshall($element);
		$object = new NumberIncorrect();
		$object->setSectionIdentifier($baseComponent->getSectionIdentifier());
		$object->setIncludeCategories($baseComponent->getIncludeCategories());
		$object->setExcludeCategories($baseComponent->getExcludeCategories());
			
		return $object;
	}
	
	public function getExpectedQTIClassName() {
		return 'numberIncorrect';
	}
}